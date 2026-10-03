<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_cart_and_see_the_created_order(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '081234567890',
        ]);
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'stock' => 5,
            'category' => 'makanan',
            'is_active' => true,
        ]);
        CartItem::query()->create([
            'user_id' => $customer->id,
            'menu_item_id' => $product->id,
            'quantity' => 2,
        ]);
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/checkout', ['address' => 'Jl. Merdeka 1'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.customer_name', $customer->name)
            ->assertJsonPath('data.restaurant_name', $seller->name)
            ->assertJsonPath('data.total_amount', 50000)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.items.0.product_name', 'Nasi Goreng')
            ->assertJsonPath('data.items.0.quantity', 2);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'seller_id' => $seller->id,
            'total_amount' => 50000,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'id' => $product->id,
            'stock' => 3,
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $customer->id,
            'menu_item_id' => $product->id,
        ]);

        $this->withToken($token)->getJson('/api/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_checkout_requires_a_nonempty_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/orders', ['address' => 'Jl. Merdeka 1'])->assertUnprocessable();
    }

    public function test_customer_can_cancel_their_pending_order_without_deleting_it(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'stock' => 2,
            'category' => 'makanan',
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'DY-260930-000102',
            'customer_name' => $customer->name,
            'restaurant_name' => $seller->name,
            'total_amount' => 25000,
            'status' => 'menunggu',
        ]);
        $order->items()->create([
            'menu_item_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);
        $token = $customer->createToken('customer-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);

        $this->withToken($token)
            ->postJson('/api/orders/'.$order->id.'/cancel')
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'id' => $product->id,
            'stock' => 3,
        ]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_customer_seller_and_driver_complete_the_order_lifecycle(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '081234567890']);
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Mie Ayam',
            'price' => 18000,
            'stock' => 4,
            'category' => 'Makanan',
            'is_active' => true,
        ]);
        CartItem::query()->create(['user_id' => $customer->id, 'menu_item_id' => $product->id, 'quantity' => 1]);
        $customerToken = $customer->createToken('customer-flow')->plainTextToken;
        $sellerToken = $seller->createToken('seller-flow')->plainTextToken;
        $driverToken = $driver->createToken('driver-flow')->plainTextToken;

        $orderResponse = $this->withToken($customerToken)->postJson('/api/checkout', [
            'address' => 'Jl. Merdeka 1, Jakarta',
            'notes' => 'Tanpa sambal',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.address', 'Jl. Merdeka 1, Jakarta');
        $orderId = $orderResponse->json('data.id');

        Auth::forgetGuards();
        $this->withToken($sellerToken)->patchJson("/api/seller/orders/{$orderId}/status", ['status' => 'confirmed'])->assertOk();
        Auth::forgetGuards();
        $this->withToken($sellerToken)->patchJson("/api/seller/orders/{$orderId}/status", ['status' => 'preparing'])->assertOk();
        Auth::forgetGuards();
        $this->withToken($sellerToken)->patchJson("/api/seller/orders/{$orderId}/status", ['status' => 'ready'])->assertOk();

        Auth::forgetGuards();
        $this->withToken($driverToken)->postJson("/api/driver/orders/{$orderId}/accept")
            ->assertOk()->assertJsonPath('data.status', 'assigned');
        Auth::forgetGuards();
        $this->withToken($driverToken)->getJson("/api/driver/orders/{$orderId}")
            ->assertOk()->assertJsonPath('data.address', 'Jl. Merdeka 1, Jakarta');
        foreach (['picked_up', 'delivering', 'completed'] as $status) {
            Auth::forgetGuards();
            $this->withToken($driverToken)->patchJson("/api/driver/orders/{$orderId}/status", ['status' => $status])
                ->assertOk()->assertJsonPath('data.status', $status);
        }

        Auth::forgetGuards();
        $this->withToken($customerToken)->getJson("/api/orders/{$orderId}")
            ->assertOk()->assertJsonPath('data.status', 'completed');
    }

    public function test_seller_can_view_an_order_containing_their_product(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'stock' => 5,
            'category' => 'makanan',
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'DY-260930-000001',
            'customer_name' => $customer->name,
            'restaurant_name' => $seller->name,
            'total_amount' => 25000,
            'status' => 'menunggu',
        ]);
        $order->items()->create([
            'menu_item_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $sellerToken = $seller->createToken('seller-test')->plainTextToken;
        $otherSeller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $otherSellerToken = $otherSeller->createToken('other-seller-test')->plainTextToken;

        $this->withToken($sellerToken)
            ->getJson('/api/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.user.id', $customer->id);

        $this->withToken($sellerToken)
            ->getJson('/api/seller/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order->id);

        $this->withToken($sellerToken)
            ->patchJson('/api/seller/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $seller->id,
            'status' => 'confirmed',
        ]);

        Auth::forgetGuards();

        $this->withToken($otherSellerToken)
            ->getJson('/api/orders/'.$order->id)
            ->assertForbidden();

        Auth::forgetGuards();

        $this->withToken($otherSellerToken)
            ->patchJson('/api/seller/orders/'.$order->id.'/status', ['status' => 'preparing'])
            ->assertForbidden();
    }

    public function test_customer_cannot_access_seller_order_routes(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);
        $token = $customer->createToken('customer-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/seller/orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'Endpoint ini hanya dapat diakses oleh akun seller aktif.');

        Auth::forgetGuards();

        $inactiveSeller = User::factory()->create([
            'role' => 'seller',
            'status' => 'suspended',
        ]);
        $inactiveToken = $inactiveSeller->createToken('inactive-seller-test')->plainTextToken;

        $this->withToken($inactiveToken)
            ->getJson('/api/seller/orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'Endpoint ini hanya dapat diakses oleh akun seller aktif.');
    }
}
