<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = $this->visibleProducts()
            ->orderByDesc('is_sponsored')
            ->orderBy('name')
            ->get()
            ->map(fn (MenuItem $product): array => $this->productData($product));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function show(int $product): JsonResponse
    {
        $product = $this->visibleProducts()->findOrFail($product);

        return response()->json([
            'success' => true,
            'data' => $this->productData($product),
        ]);
    }

    private function visibleProducts(): Builder
    {
        return MenuItem::query()
            ->with('user:id,name')
            ->where('is_active', true)
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'seller')->where('status', 'active'));
    }

    private function productData(MenuItem $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'image_url' => $product->image_url,
            'price' => $product->price,
            'stock' => $product->stock,
            'category' => $product->category,
            'is_sponsored' => $product->is_sponsored,
            'seller' => [
                'id' => $product->user->id,
                'name' => $product->user->name,
            ],
        ];
    }
}
