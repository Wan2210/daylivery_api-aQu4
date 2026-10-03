<?php

namespace Tests\Feature;

use App\Models\SponsorAd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiSponsorAdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_android_api_returns_active_ads_with_public_image_urls(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('sponsor-ads/home-banner.jpg', 'image');

        SponsorAd::query()->create([
            'title' => 'Promo Beranda',
            'description' => 'Promo minggu ini',
            'image_path' => 'sponsor-ads/home-banner.jpg',
            'target_url' => 'https://example.test/promo',
            'placement' => 'home_banner',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        SponsorAd::query()->create([
            'title' => 'Promo Draft',
            'image_path' => 'sponsor-ads/home-banner.jpg',
            'placement' => 'home_banner',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $expectedImageUrl = Storage::disk('public')->url('sponsor-ads/home-banner.jpg');

        $this->getJson('/api/ads')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Promo Beranda')
            ->assertJsonPath('data.0.image_url', $expectedImageUrl)
            ->assertJsonPath('data.0.target_url', 'https://example.test/promo')
            ->assertJsonPath('data.0.placement', 'home_banner');

        $this->getJson('/api/v1/ads')->assertOk()->assertJsonCount(1, 'data');
    }
}
