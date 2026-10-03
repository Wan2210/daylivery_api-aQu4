<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiAdminMenusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_create_menus_but_customer_cannot_access_them(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $adminToken = $admin->createToken('admin-menus-test')->plainTextToken;

        $this->withToken($adminToken)
            ->getJson('/api/admin/menus')
            ->assertOk();

        $createdMenu = $this->withToken($adminToken)
            ->postJson('/api/admin/menus', [
                'user_id' => $seller->id,
                'name' => 'Nasi Goreng',
                'description' => 'Menu test',
                'price' => 25000,
                'stock' => 5,
                'category' => 'makanan',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Nasi Goreng')
            ->assertJsonPath('data.is_sponsored', false);
        $menuId = $createdMenu->json('data.id');

        $this->withToken($adminToken)
            ->patchJson('/api/admin/menus/'.$menuId.'/sponsor', ['is_sponsored' => true])
            ->assertOk()
            ->assertJsonPath('data.is_sponsored', true);

        $this->withToken($adminToken)
            ->patchJson('/api/admin/menus/'.$menuId.'/sponsor', ['is_sponsored' => true])
            ->assertOk()
            ->assertJsonPath('data.is_sponsored', true);

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuId,
            'is_sponsored' => true,
        ]);

        Auth::forgetGuards();
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);
        $customerToken = $customer->createToken('customer-menus-test')->plainTextToken;

        $this->withToken($customerToken)
            ->getJson('/api/admin/menus')
            ->assertForbidden();

        Auth::forgetGuards();
        $this->withToken($customerToken)
            ->patchJson('/api/admin/menus/'.$menuId.'/sponsor', ['is_sponsored' => false])
            ->assertForbidden();
    }

    public function test_admin_can_toggle_sponsor_from_the_web_menu_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $menu = MenuItem::query()->create([
            'user_id' => $seller->id,
            'name' => 'Menu Sponsor Web',
            'price' => 10000,
            'stock' => 2,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.menus.sponsor', $menu), ['is_sponsored' => true])
            ->assertRedirect(route('admin.menus.index'));

        $this->assertDatabaseHas('menu_items', [
            'id' => $menu->id,
            'is_sponsored' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.menus.index'))
            ->assertOk()
            ->assertSee('Sponsor')
            ->assertSee('Hapus sponsor');
    }
}
