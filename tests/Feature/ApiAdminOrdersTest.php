<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AdminOrderController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiAdminOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_endpoints_load_assigned_driver(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'DY-261001-000001',
            'customer_name' => $customer->name,
            'restaurant_name' => 'Daylivery Kitchen',
            'total_amount' => 25000,
            'status' => 'ready',
        ]);
        $order->update(['driver_id' => $driver->id]);
        $token = $admin->createToken('admin-orders-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/admin/orders')
            ->assertOk()
            ->assertJsonPath('data.data.0.driver.id', $driver->id);

        $this->withToken($token)
            ->getJson('/api/admin/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.driver.id', $driver->id);
    }

    public function test_admin_can_advance_localized_pending_status_and_record_history(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'order_number' => 'DY-261001-000002',
            'customer_name' => 'Pelanggan Test',
            'restaurant_name' => 'Daylivery Kitchen',
            'total_amount' => 25000,
            'status' => 'menunggu',
        ]);
        $request = Request::create('/api/admin/orders/'.$order->id.'/status', 'PATCH', [
            'status' => 'confirmed',
        ]);
        $request->setUserResolver(fn (): User => $admin);

        $response = app(AdminOrderController::class)->updateStatus($request, $order);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('confirmed', $response->getData(true)['data']['status']);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $admin->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_can_assign_one_active_driver_to_ready_order_only_once(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
            'is_blocked' => false,
        ]);
        $inactiveDriver = User::factory()->create([
            'role' => 'driver',
            'status' => 'suspended',
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'DY-261001-000003',
            'customer_name' => $customer->name,
            'restaurant_name' => 'Daylivery Kitchen',
            'total_amount' => 25000,
            'status' => 'ready',
        ]);
        $token = $admin->createToken('admin-assign-driver-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/admin/orders/'.$order->id.'/assign-driver', [
                'driver_id' => $inactiveDriver->id,
            ])
            ->assertUnprocessable();

        $this->withToken($token)
            ->postJson('/api/admin/orders/'.$order->id.'/assign-driver', [
                'driver_id' => $driver->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.driver.id', $driver->id)
            ->assertJsonPath('data.status', 'assigned');

        $this->withToken($token)
            ->postJson('/api/admin/orders/'.$order->id.'/assign-driver', [
                'driver_id' => $inactiveDriver->id,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'driver_id' => $driver->id,
            'status' => 'assigned',
        ]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $admin->id,
            'status' => 'assigned',
        ]);
    }
}