<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiDriverOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_driver_can_claim_and_update_an_order(): void
    {
        $driver = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
        ]);
        $otherDriver = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'DY-260930-000101',
            'customer_name' => $customer->name,
            'restaurant_name' => 'Daylivery Kitchen',
            'total_amount' => 25000,
            'status' => 'ready',
        ]);
        $driverToken = $driver->createToken('driver-test')->plainTextToken;
        $otherDriverToken = $otherDriver->createToken('other-driver-test')->plainTextToken;

        $this->withToken($driverToken)
            ->getJson('/api/v1/driver/orders/available')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($driverToken)
            ->getJson('/api/driver/orders/available')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($driverToken)
            ->postJson('/api/v1/driver/orders/'.$order->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.driver_id', $driver->id)
            ->assertJsonPath('data.status', 'assigned');

        Auth::forgetGuards();

        $this->withToken($otherDriverToken)
            ->postJson('/api/v1/driver/orders/'.$order->id.'/accept')
            ->assertUnprocessable();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'driver_id' => $driver->id,
        ]);

        Auth::forgetGuards();

        $this->withToken($driverToken)
            ->patchJson('/api/v1/driver/orders/'.$order->id.'/status', ['status' => 'picked_up'])
            ->assertOk()
            ->assertJsonPath('data.status', 'picked_up');

        Auth::forgetGuards();

        $this->withToken($driverToken)
            ->patchJson('/api/v1/driver/orders/'.$order->id.'/status', ['status' => 'delivering'])
            ->assertOk()
            ->assertJsonPath('data.status', 'delivering');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $driver->id,
            'status' => 'assigned',
        ]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $driver->id,
            'status' => 'picked_up',
        ]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $driver->id,
            'status' => 'delivering',
        ]);
    }

    public function test_driver_can_report_and_fetch_gps_tracking(): void
    {
        $driver = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
        ]);
        $token = $driver->createToken('driver-tracking-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/driver/tracking/location', [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'accuracy' => 12.5,
                'speed_kmh' => 18.4,
                'heading' => 120,
                'battery_level' => 93,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.latitude', -6.2088)
            ->assertJsonPath('data.longitude', 106.8456);

        $this->withToken($token)
            ->getJson('/api/v1/driver/tracking/location')
            ->assertOk()
            ->assertJsonPath('data.latitude', -6.2088)
            ->assertJsonPath('data.longitude', 106.8456);

        $this->withToken($token)
            ->getJson('/api/v1/driver/tracking/history')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('driver_locations', [
            'user_id' => $driver->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
    }

    public function test_non_driver_cannot_access_driver_orders(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);
        $token = $customer->createToken('customer-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/driver/orders/available')
            ->assertForbidden()
            ->assertJsonPath('message', 'Endpoint ini hanya dapat diakses oleh akun driver aktif.');

        Auth::forgetGuards();

        $inactiveDriver = User::factory()->create([
            'role' => 'driver',
            'status' => 'suspended',
        ]);
        $inactiveToken = $inactiveDriver->createToken('inactive-driver-test')->plainTextToken;

        $this->withToken($inactiveToken)
            ->getJson('/api/driver/orders/available')
            ->assertForbidden()
            ->assertJsonPath('message', 'Endpoint ini hanya dapat diakses oleh akun driver aktif.');
    }
}
