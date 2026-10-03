<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiAdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_view_users_but_customers_cannot(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);
        $adminToken = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($adminToken)
            ->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $customer->id,
                'email' => $customer->email,
            ]);

        $this->withToken($adminToken)
            ->getJson('/api/admin/users/'.$customer->id)
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonMissingPath('data.password');

        Auth::forgetGuards();
        $customerToken = $customer->createToken('customer-test')->plainTextToken;

        $this->withToken($customerToken)
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_admin_dashboard_returns_order_summary(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $token = $admin->createToken('admin-dashboard-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.stats.orders_today', 0)
            ->assertJsonStructure([
                'data' => [
                    'stats' => ['orders_today', 'revenue_today', 'active_orders', 'completed_today'],
                    'statuses',
                    'weekly_revenue',
                    'recent_orders',
                ],
            ]);
    }
}