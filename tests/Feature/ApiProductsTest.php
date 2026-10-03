<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_products_endpoint_returns_active_seller_products_and_stock(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Nasi Goreng',
            'description' => 'Nasi goreng spesial',
            'price' => 28000,
            'stock' => 12,
            'category' => 'makanan',
            'is_active' => true,
        ]);

        MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Produk Nonaktif',
            'price' => 10000,
            'stock' => 5,
            'category' => 'makanan',
            'is_active' => false,
        ]);

        MenuItem::query()->create([
            'user_id' => $admin->id,
            'name' => 'Produk Admin',
            'price' => 10000,
            'stock' => 5,
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.stock', 12)
            ->assertJsonPath('data.0.seller.name', $seller->name);

        $this->getJson('/api/v1/products')->assertOk();
    }

    public function test_public_product_detail_endpoint_returns_active_seller_product(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $product = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Ayam Geprek',
            'description' => 'Ayam dengan sambal',
            'price' => 25000,
            'stock' => 8,
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $this->getJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.stock', 8)
            ->assertJsonPath('data.seller.name', $seller->name);

        $this->postJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $product->id);

        $this->getJson('/api/v1/products/'.$product->id)->assertOk();
    }

    public function test_public_catalog_prioritizes_sponsored_products_and_includes_the_flag(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $regularProduct = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Produk Biasa',
            'price' => 10000,
            'stock' => 5,
            'category' => 'makanan',
            'is_active' => true,
        ]);
        $sponsoredProduct = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Produk Sponsor',
            'price' => 15000,
            'stock' => 5,
            'category' => 'makanan',
            'is_active' => true,
            'is_sponsored' => true,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('data.0.id', $sponsoredProduct->id)
            ->assertJsonPath('data.0.is_sponsored', true)
            ->assertJsonPath('data.1.id', $regularProduct->id)
            ->assertJsonPath('data.1.is_sponsored', false);
    }
}
