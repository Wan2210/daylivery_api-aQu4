<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApiSellerMenusTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_manage_only_their_menus_and_delete_is_non_destructive(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        $otherSeller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        Category::query()->create(['name' => 'Makanan']);
        $sellerToken = $seller->createToken('seller-menu-test')->plainTextToken;

        $created = $this->withToken($sellerToken)
            ->postJson('/api/seller/menus', [
                'name' => 'Nasi Goreng',
                'description' => 'Menu seller',
                'price' => 25000,
                'stock' => 5,
                'category' => 'Makanan',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $seller->id)
            ->assertJsonPath('data.is_active', true);
        $menuId = $created->json('data.id');

        $this->withToken($sellerToken)
            ->getJson('/api/seller/menus')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $menuId);

        $this->withToken($sellerToken)
            ->getJson('/api/seller/menus/'.$menuId)
            ->assertOk()
            ->assertJsonPath('data.id', $menuId);

        $this->withToken($sellerToken)
            ->patchJson('/api/seller/menus/'.$menuId, [
                'price' => 28000,
                'is_sponsored' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.price', 28000)
            ->assertJsonPath('data.is_sponsored', false);

        $this->withToken($sellerToken)
            ->patchJson('/api/seller/menus/'.$menuId.'/stock', ['stock' => 3])
            ->assertOk()
            ->assertJsonPath('data.stock', 3);

        Auth::forgetGuards();
        $otherToken = $otherSeller->createToken('other-seller-menu-test')->plainTextToken;
        $this->withToken($otherToken)
            ->getJson('/api/seller/menus/'.$menuId)
            ->assertNotFound();

        Auth::forgetGuards();
        $this->withToken($sellerToken)
            ->deleteJson('/api/seller/menus/'.$menuId)
            ->assertOk();

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuId,
            'user_id' => $seller->id,
            'is_active' => false,
        ]);
    }

    public function test_seller_can_upload_replace_and_remove_product_image_for_customer_catalog(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        Category::query()->create(['name' => 'Makanan']);
        $sellerToken = $seller->createToken('seller-image-test')->plainTextToken;
        $customerToken = $customer->createToken('customer-image-test')->plainTextToken;

        $created = $this->withHeaders(['Accept' => 'application/json'])
            ->withToken($sellerToken)
            ->post('/api/seller/menus', [
                'name' => 'Soto Ayam',
                'price' => 22000,
                'stock' => 6,
                'category' => 'Makanan',
                'image' => $this->pngFile('soto.png'),
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);
        $menuId = $created->json('data.id');
        $oldPath = $created->json('data.image_path');

        Storage::disk('public')->assertExists($oldPath);
        $this->getJson('/api/products/'.$menuId)
            ->assertOk()
            ->assertJsonPath('data.image_url', Storage::disk('public')->url($oldPath));

        Auth::forgetGuards();
        $this->withToken($customerToken)
            ->postJson('/api/cart', ['product_id' => $menuId])
            ->assertCreated()
            ->assertJsonPath('data.image_url', Storage::disk('public')->url($oldPath));

        Auth::forgetGuards();
        $updated = $this->withHeaders(['Accept' => 'application/json'])
            ->withToken($sellerToken)
            ->patch('/api/seller/menus/'.$menuId, ['image' => $this->pngFile('soto-baru.png')])
            ->assertOk();
        $newPath = $updated->json('data.image_path');
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        Auth::forgetGuards();
        $this->withToken($sellerToken)
            ->patchJson('/api/seller/menus/'.$menuId, ['remove_image' => true])
            ->assertOk()
            ->assertJsonPath('data.image_url', null);
        Storage::disk('public')->assertMissing($newPath);
    }

    private function pngFile(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC', true);

        return UploadedFile::fake()->createWithContent($name, $content);
    }
}
