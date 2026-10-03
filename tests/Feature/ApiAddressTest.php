<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_only_their_own_addresses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $otherToken = $otherUser->createToken('test')->plainTextToken;

        $this->getJson('/api/v1/auth/addresses')->assertUnauthorized();

        $created = $this->withToken($token)->postJson('/api/v1/auth/addresses', [
            'recipient_name' => 'Pelanggan Tes',
            'phone' => '081234567890',
            'address' => 'Jalan Merdeka 1',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
        ])->assertCreated()
            ->assertJsonPath('data.is_default', true);

        $addressId = $created->json('data.id');

        $this->withToken($token)
            ->getJson('/api/v1/auth/addresses')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        Auth::forgetGuards();
        $this->withToken($otherToken)
            ->getJson('/api/v1/auth/addresses/'.$addressId)
            ->assertNotFound();

        Auth::forgetGuards();
        $this->withToken($token)
            ->patchJson('/api/v1/auth/addresses/'.$addressId, ['label' => 'Kantor'])
            ->assertOk()
            ->assertJsonPath('data.label', 'Kantor');

        $this->withToken($token)
            ->deleteJson('/api/v1/auth/addresses/'.$addressId)
            ->assertOk();

        $this->assertDatabaseMissing('addresses', ['id' => $addressId]);
    }

    public function test_typoed_address_route_alias_is_supported(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->getJson('/api/v1/auth/addresis')->assertUnauthorized();

        $this->withToken($token)
            ->getJson('/api/v1/auth/addresis')
            ->assertOk();
    }
}
