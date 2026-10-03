<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->cartItems()
            ->with('menuItem.user:id,name', 'menuItem.user.sellerApplication:id,user_id,shop_name,status')
            ->get()
            ->map(fn (CartItem $item): array => $this->itemData($item));

        return response()->json([
            'success' => true,
            'data' => $items,
            'total' => $items->sum('subtotal'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);
        $quantity = $validated['quantity'] ?? 1;

        $item = DB::transaction(function () use ($request, $validated, $quantity): CartItem {
            $product = $this->visibleProducts()->lockForUpdate()->findOrFail($validated['product_id']);
            $item = $request->user()->cartItems()
                ->where('menu_item_id', $product->id)
                ->lockForUpdate()
                ->first();
            $newQuantity = ($item?->quantity ?? 0) + $quantity;

            $this->ensureStock($product, $newQuantity);

            if (! $item) {
                $item = new CartItem([
                    'menu_item_id' => $product->id,
                    'quantity' => $newQuantity,
                ]);
                $request->user()->cartItems()->save($item);
            } else {
                $item->quantity = $newQuantity;
                $item->save();
            }

            return $item->load('menuItem.user:id,name', 'menuItem.user.sellerApplication:id,user_id,shop_name,status');
        });

        return response()->json([
            'success' => true,
            'data' => $this->itemData($item),
        ], 201);
    }

    public function update(Request $request, int $product): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = DB::transaction(function () use ($request, $product, $validated): CartItem {
            $menuItem = $this->visibleProducts()->lockForUpdate()->findOrFail($product);
            $this->ensureStock($menuItem, $validated['quantity']);

            $item = $request->user()->cartItems()
                ->where('menu_item_id', $menuItem->id)
                ->lockForUpdate()
                ->firstOrFail();
            $item->quantity = $validated['quantity'];
            $item->save();

            return $item->load('menuItem.user:id,name', 'menuItem.user.sellerApplication:id,user_id,shop_name,status');
        });

        return response()->json([
            'success' => true,
            'data' => $this->itemData($item),
        ]);
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $deleted = $request->user()->cartItems()
            ->where('menu_item_id', $product)
            ->delete();

        abort_unless($deleted, 404);

        return response()->json([
            'success' => true,
            'message' => 'Produk dihapus dari keranjang.',
        ]);
    }

    private function visibleProducts(): Builder
    {
        return MenuItem::query()
            ->with('user:id,name', 'user.sellerApplication:id,user_id,shop_name,status')
            ->where('is_active', true)
            ->whereHas('user', fn (Builder $query) => $query
                ->where('status', 'active')
                ->where(fn (Builder $accessQuery) => $accessQuery
                    ->where('role', 'seller')
                    ->orWhereHas('sellerApplication', fn (Builder $applicationQuery) => $applicationQuery->where('status', 'approved'))));
    }

    private function ensureStock(MenuItem $product, int $quantity): void
    {
        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah melebihi stok produk yang tersedia.',
            ]);
        }
    }

    private function itemData(CartItem $item): array
    {
        $product = $item->menuItem;

        return [
            'product_id' => $product->id,
            'name' => $product->name,
            'image_url' => $product->image_url,
            'price' => $product->price,
            'quantity' => $item->quantity,
            'subtotal' => $product->price * $item->quantity,
            'stock' => $product->stock,
            'seller' => [
                'id' => $product->user->id,
                'name' => $product->user->sellerApplication?->shop_name ?? $product->user->name,
            ],
        ];
    }
}