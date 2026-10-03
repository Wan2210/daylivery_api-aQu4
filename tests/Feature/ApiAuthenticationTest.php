<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_authenticate_and_revoke_a_token(): void
    {
        $this->getJson('/api/health')->assertOk()->assertJson(['status' => 'ok']);

        $registration = $this->postJson('/api/v1/auth/register', [
            'name' => 'Pelanggan API',
            'email' => 'customer@example.test',
            'password' => 'correct-password',
            'password_confirmation' => 'correct-password',
        ])->assertCreated()
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('user.status', 'active');

        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.test',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $token = $registration->json('token');
        $this->assertIsString($token);

        $this->withToken($token)
            ->getJson('/api/v1/auth/user')
            ->assertOk()
            ->assertJsonPath('user.email', 'customer@example.test');

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->withToken($token)->getJson('/api/v1/auth/user')->assertUnauthorized();
    }

    public function test_only_active_customers_can_login_through_the_api(): void
    {
        User::factory()->create([
            'email' => 'blocked@example.test',
            'password' => 'correct-password',
            'role' => 'customer',
            'status' => 'blocked',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'blocked@example.test',
            'password' => 'correct-password',
        ])->assertUnauthorized();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'missing@example.test',
            'password' => 'incorrect-password',
        ])->assertUnauthorized();
    }

    public function test_public_registration_cannot_assign_seller_or_driver_roles(): void
    {
        foreach (['seller', 'driver'] as $role) {
            $email = $role.'-self-register@example.test';

            $this->postJson('/api/v1/register', [
                'name' => 'Mitra Baru',
                'email' => $email,
                'password' => 'correct-password',
                'password_confirmation' => 'correct-password',
                'role' => $role,
            ])->assertUnprocessable()->assertJsonValidationErrors('role');

            $this->assertDatabaseMissing('users', ['email' => $email]);
        }

        $customerRegistration = $this->postJson('/api/v1/register', [
            'name' => 'Customer Eksplisit',
            'email' => 'explicit-customer@example.test',
            'password' => 'correct-password',
            'password_confirmation' => 'correct-password',
            'role' => 'customer',
        ])->assertCreated();

        $this->assertSame('customer', $customerRegistration->json('user.role'));
    }

    public function test_api_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/login', [
                'email' => 'missing@example.test',
                'password' => 'incorrect-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/login', [
            'email' => 'missing@example.test',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }
}
