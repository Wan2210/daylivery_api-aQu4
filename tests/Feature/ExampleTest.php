<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\SampleMenuItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_page_renders_accessible_sign_in_controls(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Selamat datang kembali')
            ->assertSee('Email admin')
            ->assertSee('Kata sandi')
            ->assertSee('data-password-toggle', false);
    }

    public function test_admin_can_sign_in_and_view_live_dashboard_data(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Tes',
            'email' => 'admin@example.test',
            'password' => 'correct-password',
            'role' => 'admin',
            'status' => 'active',
        ]);
        Order::query()->create([
            'order_number' => 'DY-TEST-000001',
            'customer_name' => 'Pelanggan Tes',
            'restaurant_name' => 'Restoran Tes',
            'total_amount' => 45000,
            'status' => 'diproses',
        ]);

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Tes')
            ->assertSee('DY-TEST-000001')
            ->assertSee('Pelanggan Tes')
            ->assertDontSee('DY-240928-084');
    }

    public function test_non_admin_users_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_create_update_and_export_orders(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.create'))
            ->assertOk()
            ->assertSee('order-create-page', false);

        $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_name' => 'Pelanggan Baru',
            'customer_phone' => '08123456789',
            'restaurant_name' => 'Warung Baru',
            'total_amount' => 75000,
            'status' => 'menunggu',
        ])->assertRedirect(route('admin.dashboard'));

        $order = Order::query()->sole();
        $this->assertSame('DY-'.now()->format('ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT), $order->order_number);

        $this->patch(route('admin.orders.status', $order), [
            'status' => 'diantar',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'diantar',
        ]);

        $this->get(route('admin.dashboard', ['q' => '08123456789']))
            ->assertOk()
            ->assertSee($order->order_number);

        $order->update(['customer_name' => '=1+1']);

        $export = $this->get(route('admin.orders.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString("'=1+1", $export->streamedContent());
    }

    public function test_login_rejects_inactive_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'inactive@example.test',
            'password' => 'correct-password',
            'role' => 'admin',
            'status' => 'inactive',
        ]);

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'correct-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_dashboard_shows_real_links_for_roles_and_settings(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('/admin/users?role=driver', false)
            ->assertSee('/admin/users?role=seller', false)
            ->assertSee('/admin/users?role=customer', false)
            ->assertSee('/admin/settings', false)
            ->assertSee('id="dashboardWorkspace"', false)
            ->assertSee('function loadAdminPage', false)
            ->assertDontSee('Segera');
    }

    public function test_admin_can_change_driver_status_when_rating_is_low(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $driver = User::factory()->create([
            'role' => 'driver',
            'status' => 'blocked',
            'rating' => 2,
            'warning_level' => 'normal',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $driver), [
                'name' => $driver->name,
                'email' => $driver->email,
                'phone' => $driver->phone,
                'role' => 'driver',
                'status' => 'active',
                'rating' => 2,
                'warning_level' => 'normal',
                'warning_note' => '',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $driver->id,
            'status' => 'active',
            'is_blocked' => false,
        ]);
    }

    public function test_admin_can_create_a_shop_menu_item(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Khusus',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $seller = User::factory()->create([
            'name' => 'Penjual Terdaftar',
            'role' => 'seller',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.menus.create'))
            ->assertOk()
            ->assertSee('Tambah produk')
            ->assertSee('Stok tersedia')
            ->assertSee('Penjual Terdaftar')
            ->assertDontSee('Admin Khusus');

        $this->actingAs($admin)
            ->post(route('admin.menus.store'), [
                'user_id' => $seller->id,
                'name' => 'Ayam Geprek',
                'description' => 'Ayam krispi dengan sambal pedas',
                'price' => 25000,
                'stock' => 12,
                'category' => 'makanan',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.menus.index'));

        $this->assertDatabaseHas('menu_items', [
            'user_id' => $seller->id,
            'name' => 'Ayam Geprek',
            'price' => 25000,
            'stock' => 12,
            'category' => 'makanan',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.menus.store'), [
                'user_id' => $admin->id,
                'name' => 'Produk Admin',
                'price' => 10000,
                'category' => 'makanan',
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('menu_items', [
            'user_id' => $admin->id,
            'name' => 'Produk Admin',
        ]);
    }

    public function test_customer_count_cannot_exceed_2000(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        User::factory()->count(2000)->create(['role' => 'customer']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Pelanggan Baru',
            'email' => 'customer-2001@example.test',
            'password' => 'correct-password',
            'password_confirmation' => 'correct-password',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Jumlah pelanggan sudah mencapai batas 2.000 akun.');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Pelanggan Admin',
                'email' => 'admin-customer-2001@example.test',
                'password' => 'correct-password',
                'password_confirmation' => 'correct-password',
                'role' => 'customer',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('role');

        $seller = User::factory()->create(['role' => 'seller']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $seller), [
                'name' => $seller->name,
                'email' => $seller->email,
                'role' => 'customer',
                'status' => 'active',
                'rating' => 0,
                'warning_level' => 'normal',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(2000, User::query()->where('role', 'customer')->count());
        $this->assertSame('seller', $seller->fresh()->role);
    }

    public function test_driver_count_cannot_exceed_15(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        User::factory()->count(15)->create(['role' => 'driver']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Driver Baru',
            'email' => 'driver-16@example.test',
            'password' => 'correct-password',
            'password_confirmation' => 'correct-password',
            'role' => 'driver',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'driver-16@example.test']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Driver Admin',
                'email' => 'admin-driver-16@example.test',
                'password' => 'correct-password',
                'password_confirmation' => 'correct-password',
                'role' => 'driver',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('role');

        $seller = User::factory()->create(['role' => 'seller']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $seller), [
                'name' => $seller->name,
                'email' => $seller->email,
                'role' => 'driver',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(15, User::query()->where('role', 'driver')->count());
        $this->assertSame('seller', $seller->fresh()->role);
    }

    public function test_sample_menu_seeder_adds_three_products_to_a_seller_without_duplicates(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $this->seed(SampleMenuItemsSeeder::class);
        $this->seed(SampleMenuItemsSeeder::class);

        $this->assertSame(3, $seller->menuItems()->count());
        $this->assertDatabaseHas('menu_items', [
            'user_id' => $seller->id,
            'name' => 'Nasi Goreng Spesial',
            'price' => 28000,
            'stock' => 25,
        ]);
        $this->assertDatabaseHas('menu_items', [
            'user_id' => $seller->id,
            'name' => 'Ayam Geprek',
            'price' => 25000,
            'stock' => 18,
        ]);
        $this->assertDatabaseHas('menu_items', [
            'user_id' => $seller->id,
            'name' => 'Es Teh Manis',
            'price' => 8000,
            'stock' => 30,
        ]);
    }
}
