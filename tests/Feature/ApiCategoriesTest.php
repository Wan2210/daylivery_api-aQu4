<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_endpoint_returns_categories_from_active_seller_products(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['makanan', 'makanan', 'minuman'] as $category) {
            MenuItem::query()->create([
                'user_id' => $seller->id,
                'name' => fake()->unique()->word(),
                'price' => 10000,
                'stock' => 5,
                'category' => $category,
                'is_active' => true,
            ]);
        }

        MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Produk Nonaktif',
            'price' => 10000,
            'stock' => 5,
            'category' => 'kategori-nonaktif',
            'is_active' => false,
        ]);

        MenuItem::query()->create([
            'user_id' => $admin->id,
            'name' => 'Produk Admin',
            'price' => 10000,
            'stock' => 5,
            'category' => 'kategori-admin',
            'is_active' => true,
        ]);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'makanan')
            ->assertJsonPath('data.0.products_count', 2)
            ->assertJsonPath('data.1.name', 'minuman')
            ->assertJsonPath('data.1.products_count', 1);

        $this->getJson('/api/v1/categories')->assertOk();
    }
}
