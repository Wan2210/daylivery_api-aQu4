<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $productCategories = MenuItem::query()
            ->where('is_active', true)
            ->whereHas('user', fn (Builder $query) => $query
                ->where('status', 'active')
                ->where(fn (Builder $accessQuery) => $accessQuery
                    ->where('role', 'seller')
                    ->orWhereHas('sellerApplication', fn (Builder $applicationQuery) => $applicationQuery->where('status', 'approved'))))
            ->select('category')
            ->selectRaw('COUNT(*) AS products_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->keyBy('category');

        $categories = Category::query()
            ->orderBy('name')
            ->pluck('name')
            ->merge($productCategories->keys())
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $name): array => [
                'name' => $name,
                'products_count' => (int) ($productCategories->get($name)?->products_count ?? 0),
            ]);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
