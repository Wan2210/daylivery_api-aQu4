<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_manage_their_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $token = $customer->createToken('test')->plainTextToken;
        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'stock' => 5,
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $this->getJson('/api/cart')->assertUnauthorized();

        $this->withToken($token)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated()
            ->assertJsonPath('data.quantity', 2)
            ->assertJsonPath('data.subtotal', 50000);

        $this->withToken($token)->postJson('/api/cart', [
            'product_id' => $product->id,
        ])->assertCreated()
            ->assertJsonPath('data.quantity', 3);

        $this->withToken($token)->getJson('/api/cart')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('total', 75000);

        $this->withToken($token)->patchJson('/api/cart/'.$product->id, ['quantity' => 4])
            ->assertOk()
            ->assertJsonPath('data.quantity', 4);

        $this->withToken($token)->putJson('/api/cart/'.$product->id, ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('data.quantity', 3);

        $this->withToken($token)->deleteJson('/api/cart/'.$product->id)->assertOk();

        $this->withToken($token)->getJson('/api/cart')->assertJsonCount(0, 'data');
    }

    public function test_cart_rejects_quantities_above_available_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $token = $customer->createToken('test')->plainTextToken;
        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Ayam Geprek',
            'price' => 20000,
            'stock' => 1,
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $this->withToken($token)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertUnprocessable();
    }
}