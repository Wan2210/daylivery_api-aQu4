<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    /**
     * Menampilkan semua menu.
     */
    public function index(Request $request)
    {
        $query = MenuItem::with([
            'user:id,name,email,role',
        ]);

        // Filter seller
        if ($request->filled('seller_id')) {
            $query->where('user_id', $request->seller_id);
        }

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Pencarian nama menu
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $menus = $query
            ->latest()
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar menu berhasil diambil.',
            'data' => $menus,
        ]);
    }

    /**
     * Detail menu.
     */
    public function show(MenuItem $menuItem)
    {
        $menuItem->load([
            'user:id,name,email,role',
        ]);

        return response()->json([
            'message' => 'Detail menu berhasil diambil.',
            'data' => $menuItem,
        ]);
    }

    /**
     * Membuat menu baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $seller = User::query()->find($validated['user_id']);

        if (! $seller || $seller->status !== 'active' || $seller->is_blocked || ! $seller->hasSellerAccess() || $seller->suspended_until?->isFuture()) {
            return response()->json([
                'message' => 'User yang dipilih bukan seller.',
            ], 422);
        }

        if ($seller->is_blocked) {
            return response()->json([
                'message' => 'Seller sedang diblokir.',
            ], 422);
        }

        if (
            $seller->suspended_until &&
            $seller->suspended_until->isFuture()
        ) {
            return response()->json([
                'message' => 'Seller sedang disuspend.',
                'suspended_until' => $seller->suspended_until,
            ], 422);
        }

        $menu = MenuItem::query()->create([
            ...$validated,
            'is_sponsored' => false,
        ]);

        return response()->json([
            'message' => 'Menu berhasil dibuat.',
            'data' => $menu->load([
                'user:id,name,email,role',
            ]),
        ], 201);
    }

    /**
     * Update menu.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'user_id' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],

            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'category' => [
                'sometimes',
                'string',
                'max:100',
            ],
        ]);

        // Jika seller diganti, pastikan user tersebut seller.
        if (isset($validated['user_id'])) {
            $seller = User::query()->find($validated['user_id']);

            if (! $seller || $seller->status !== 'active' || ! $seller->hasSellerAccess() || $seller->is_blocked || $seller->suspended_until?->isFuture()) {
                return response()->json([
                    'message' => 'User yang dipilih bukan seller.',
                ], 422);
            }
        }

        $menuItem->update($validated);

        return response()->json([
            'message' => 'Menu berhasil diperbarui.',
            'data' => $menuItem->fresh()->load([
                'user:id,name,email,role',
            ]),
        ]);
    }

    /**
     * Hapus menu.
     */
    public function destroy(MenuItem $menuItem)
    {
        // Cegah penghapusan jika menu sudah pernah digunakan
        // dalam order.
        $usedInOrder = $menuItem->orderItems()->exists();

        if ($usedInOrder) {
            return response()->json([
                'message' => 'Menu tidak dapat dihapus karena sudah digunakan dalam pesanan.',
            ], 422);
        }

        $menuItem->delete();

        return response()->json([
            'message' => 'Menu berhasil dihapus.',
        ]);
    }

    /**
     * Update stok.
     */
    public function updateStock(
        Request $request,
        MenuItem $menuItem
    ) {
        $request->validate([
            'stock' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $menuItem->update([
            'stock' => $request->stock,
        ]);

        return response()->json([
            'message' => 'Stok menu berhasil diperbarui.',
            'data' => $menuItem->fresh(),
        ]);
    }

    public function updateSponsor(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'is_sponsored' => ['required', 'boolean'],
        ]);

        $menuItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => $menuItem->is_sponsored
                ? 'Menu berhasil ditandai sebagai sponsor.'
                : 'Status sponsor menu berhasil dinonaktifkan.',
            'data' => $menuItem->fresh(),
        ]);
    }
}
