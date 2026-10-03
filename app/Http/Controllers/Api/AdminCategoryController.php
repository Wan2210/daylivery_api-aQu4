<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->categoriesQuery()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        $category = Category::query()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $this->categoriesQuery()->whereKey($category->id)->firstOrFail(),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->categoriesQuery()->whereKey($category->id)->firstOrFail(),
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);
        $oldName = $category->name;

        DB::transaction(function () use ($category, $data, $oldName): void {
            MenuItem::query()->where('category', $oldName)->update(['category' => $data['name']]);
            $category->update($data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $this->categoriesQuery()->whereKey($category->id)->firstOrFail(),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->menuItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori masih digunakan menu dan tidak dapat dihapus.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    private function categoriesQuery(): Builder
    {
        return Category::query()->withCount([
            'menuItems as products_count' => fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('user', fn (Builder $sellerQuery) => $sellerQuery
                    ->where('status', 'active')
                    ->where(fn (Builder $accessQuery) => $accessQuery
                        ->where('role', 'seller')
                        ->orWhereHas('sellerApplication', fn (Builder $applicationQuery) => $applicationQuery->where('status', 'approved')))),
        ]);
    }
}
