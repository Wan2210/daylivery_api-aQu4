<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiAdminCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories_without_orphaning_existing_menus(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $token = $admin->createToken('admin-categories-test')->plainTextToken;
        $categoryResponse = $this->withToken($token)
            ->postJson('/api/admin/categories', ['name' => 'Makanan'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Makanan');
        $categoryId = $categoryResponse->json('data.id');

        $menu = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'stock' => 5,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/admin/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Makanan')
            ->assertJsonPath('data.0.products_count', 1);

        $this->withToken($token)
            ->patchJson('/api/admin/categories/'.$categoryId, ['name' => 'Masakan'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Masakan');

        $this->assertDatabaseHas('menu_items', [
            'id' => $menu->id,
            'category' => 'Masakan',
        ]);

        $this->withToken($token)
            ->deleteJson('/api/admin/categories/'.$categoryId)
            ->assertUnprocessable();

        $unusedCategory = Category::query()->create(['name' => 'Minuman']);
        $this->withToken($token)
            ->deleteJson('/api/admin/categories/'.$unusedCategory->id)
            ->assertOk();

        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'name' => 'Masakan']);
        $this->assertDatabaseMissing('categories', ['id' => $unusedCategory->id]);

        Auth::forgetGuards();
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $customerToken = $customer->createToken('customer-test')->plainTextToken;
        $this->withToken($customerToken)->getJson('/api/admin/categories')->assertForbidden();
    }
}
