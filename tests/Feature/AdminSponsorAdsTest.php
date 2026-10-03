<?php

namespace Tests\Feature;

use App\Models\SponsorAd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSponsorAdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_edit_and_archive_an_ad(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.ads.store'), [
                'title' => 'Promo Beranda',
                'description' => 'Promo pembukaan',
                'image' => UploadedFile::fake()->create('promo.jpg', 100, 'image/jpeg'),
                'target_url' => 'https://example.test/promo',
                'placement' => 'home_banner',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.ads.index'));

        $ad = SponsorAd::query()->firstOrFail();
        Storage::disk('public')->assertExists($ad->image_path);

        $this->actingAs($admin)
            ->get(route('admin.ads.index'))
            ->assertOk()
            ->assertSee('Promo Beranda');

        $this->actingAs($admin)
            ->put(route('admin.ads.update', $ad), [
                'title' => 'Promo Diperbarui',
                'description' => 'Promo baru',
                'target_url' => 'https://example.test/promo-baru',
                'placement' => 'category_banner',
                'sort_order' => 2,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.ads.index'));

        $this->assertDatabaseHas('sponsor_ads', [
            'id' => $ad->id,
            'title' => 'Promo Diperbarui',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.ads.destroy', $ad))
            ->assertRedirect(route('admin.ads.index'));

        $this->assertSoftDeleted('sponsor_ads', ['id' => $ad->id]);
        Storage::disk('public')->assertExists($ad->image_path);
    }

    public function test_admin_cannot_save_unsafe_ad_target_urls(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.ads.store'), [
                'title' => 'Unsafe promo',
                'image' => UploadedFile::fake()->create('promo.jpg', 100, 'image/jpeg'),
                'target_url' => 'javascript:alert(1)',
                'placement' => 'home_banner',
                'sort_order' => 0,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('target_url');

        $this->assertDatabaseMissing('sponsor_ads', ['title' => 'Unsafe promo']);
    }
}
