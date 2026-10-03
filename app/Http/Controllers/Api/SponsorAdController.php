<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SponsorAd;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SponsorAdController extends Controller
{
    public function index(): JsonResponse
    {
        $ads = SponsorAd::query()
            ->activeForDelivery()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (SponsorAd $ad): array => [
                'id' => $ad->id,
                'title' => $ad->title,
                'description' => $ad->description,
                'image_url' => Storage::disk('public')->url($ad->image_path),
                'target_url' => $ad->target_url,
                'placement' => $ad->placement,
                'sort_order' => $ad->sort_order,
            ]);

        return response()->json([
            'success' => true,
            'data' => $ads,
        ]);
    }
}
