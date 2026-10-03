<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SellerMenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $menus = $request->user()->menuItems()
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $menus]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products/'.$request->user()->id, 'public');
        }
        unset($data['image']);
        $data['is_active'] = $data['is_active'] ?? true;
        $menu = $request->user()->menuItems()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $menu,
        ], 201);
    }

    public function show(Request $request, int $menuItem): JsonResponse
    {
        $menu = $request->user()->menuItems()->findOrFail($menuItem);

        return response()->json(['success' => true, 'data' => $menu]);
    }

    public function update(Request $request, int $menuItem): JsonResponse
    {
        $menu = $request->user()->menuItems()->findOrFail($menuItem);
        $data = $request->validate($this->rules(false));
        $oldImagePath = $menu->image_path;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products/'.$request->user()->id, 'public');
        } elseif ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        unset($data['image'], $data['remove_image']);
        $menu->fill($data)->save();

        if ($oldImagePath && $oldImagePath !== $menu->image_path) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui.',
            'data' => $menu->fresh(),
        ]);
    }

    public function updateStock(Request $request, int $menuItem): JsonResponse
    {
        $menu = $request->user()->menuItems()->findOrFail($menuItem);
        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);
        $menu->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Stok menu berhasil diperbarui.',
            'data' => $menu->fresh(),
        ]);
    }

    public function destroy(Request $request, int $menuItem): JsonResponse
    {
        $menu = $request->user()->menuItems()->findOrFail($menuItem);
        $menu->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Menu dinonaktifkan; data order tetap tersimpan.',
        ]);
    }

    private function rules(bool $creating = true): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return [
            'name' => [$required, 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
            'price' => [$required, 'integer', 'min:0'],
            'stock' => [$required, 'integer', 'min:0'],
            'category' => [$required, 'string', Rule::exists('categories', 'name')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
