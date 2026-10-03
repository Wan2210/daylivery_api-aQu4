<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\SellerApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiPartnerApplicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_apply_for_seller_and_driver_and_admin_can_approve_both(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customerToken = $customer->createToken('customer-test')->plainTextToken;
        $adminToken = $admin->createToken('admin-test')->plainTextToken;

        $sellerApplication = $this->withToken($customerToken)
            ->postJson('/api/seller/application', [
                'shop_name' => 'Warung Pagi',
                'phone' => '081234567890',
                'shop_address' => 'Jl. Merdeka 1',
                'business_category' => 'Makanan',
                'description' => 'Sarapan',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $sellerId = $sellerApplication->json('data.id');
        $this->withToken($adminToken)
            ->postJson("/api/admin/applications/seller/{$sellerId}/review", [
                'status' => 'rejected',
                'admin_note' => 'Mohon lengkapi alamat.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        Auth::forgetGuards();
        $this->withToken($customerToken)
            ->postJson('/api/seller/application', [
                'shop_name' => 'Warung Pagi',
                'phone' => '081234567890',
                'shop_address' => 'Jl. Merdeka 1, Jakarta',
                'business_category' => 'Makanan',
            ])
            ->assertCreated()
            ->assertJsonPath('data.id', $sellerId)
            ->assertJsonPath('data.status', 'pending');

        Auth::forgetGuards();
        $this->withToken($adminToken)
            ->postJson("/api/admin/applications/seller/{$sellerId}/review", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        Auth::forgetGuards();
        $this->withToken($customerToken)
            ->getJson('/api/seller/menus')
            ->assertOk()
            ->assertJsonPath('success', true);

        Category::query()->create(['name' => 'Makanan']);
        Auth::forgetGuards();
        $product = $this->withToken($customerToken)
            ->postJson('/api/seller/menus', [
                'name' => 'Nasi Uduk',
                'price' => 15000,
                'stock' => 5,
                'category' => 'Makanan',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $customer->id);

        Auth::forgetGuards();
        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('data.0.seller.name', 'Warung Pagi');

        Auth::forgetGuards();
        $this->withToken($customerToken)
            ->postJson('/api/cart', ['product_id' => $product->json('data.id')])
            ->assertCreated()
            ->assertJsonPath('data.seller.name', 'Warung Pagi');

        Auth::forgetGuards();
        $driverApplication = $this->withToken($customerToken)
            ->postJson('/api/driver/application', [
                'full_name' => $customer->name,
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka 1',
                'identity_number' => 'ID-DRIVER-001',
                'license_number' => 'SIM-001',
                'vehicle_type' => 'Motor',
                'vehicle_number' => 'B1234XYZ',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $driverId = $driverApplication->json('data.id');
        Auth::forgetGuards();
        $this->withToken($adminToken)
            ->postJson("/api/admin/applications/driver/{$driverId}/review", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        Auth::forgetGuards();
        $this->withToken($customerToken)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('user.roles.1', 'seller')
            ->assertJsonPath('user.roles.2', 'driver')
            ->assertJsonPath('user.seller_access', true)
            ->assertJsonPath('user.driver_access', true);

        Auth::forgetGuards();
        $this->withToken($customerToken)
            ->getJson('/api/driver/orders/available')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'role' => 'customer']);
    }

    public function test_admin_web_can_review_partner_application(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $application = SellerApplication::query()->create([
            'user_id' => $customer->id,
            'shop_name' => 'Warung Web',
            'phone' => '081234567890',
            'shop_address' => 'Jl. Merdeka 1',
            'business_category' => 'Makanan',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.applications.show', ['seller', $application->id]))
            ->assertOk()
            ->assertSee('Warung Web');

        $this->actingAs($admin)
            ->post(route('admin.applications.review', ['seller', $application->id]), [
                'status' => 'approved',
                'admin_note' => 'Dokumen valid.',
            ])
            ->assertRedirect(route('admin.applications.show', ['seller', $application->id]));

        $this->assertDatabaseHas('seller_applications', [
            'id' => $application->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }
}