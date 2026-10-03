<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\PushNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->customerOrders()
            ->with(['items', 'seller:id,name', 'driver:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id === $user->id) {
            // Customers retain access to their own order even after partner approval.
        } elseif ($user->hasSellerAccess()) {
            $hasSellerItem = $order->items()
                ->whereHas('menuItem', fn (Builder $query) => $query->where('user_id', $user->id))
                ->exists();

            abort_unless($order->seller_id === $user->id || $hasSellerItem, 403);
        } else {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load([
                'user:id,name,email',
                'seller:id,name',
                'driver:id,name',
                'items.menuItem',
                'statusHistory',
            ]),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $cancelledOrder = DB::transaction(function () use ($request, $order): Order {
            $lockedOrder = $request->user()->customerOrders()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            if (! in_array($lockedOrder->status, ['menunggu', 'pending'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan yang sudah diproses tidak dapat dibatalkan.',
                ]);
            }

            foreach ($lockedOrder->items as $item) {
                if ($item->menu_item_id !== null) {
                    MenuItem::query()
                        ->whereKey($item->menu_item_id)
                        ->lockForUpdate()
                        ->first()?->increment('stock', $item->quantity);
                }
            }

            $lockedOrder->update(['status' => 'cancelled']);
            $lockedOrder->statusHistory()->create([
                'user_id' => $request->user()->id,
                'status' => 'cancelled',
            ]);

                return $lockedOrder->fresh()->load(['items', 'seller:id,name', 'driver:id,name']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.',
            'data' => $cancelledOrder,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address' => ['required_without:address_id', 'nullable', 'string', 'max:2000'],
            'address_id' => ['required_without:address', 'nullable', 'integer', 'exists:addresses,id'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $order = DB::transaction(function () use ($request, $data): Order {
            $customer = $request->user();
            $cartItems = $customer->cartItems()->lockForUpdate()->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang masih kosong.',
                ]);
            }

            $preparedItems = [];
            $sellerId = null;
            $restaurantName = null;
            $total = 0;

            foreach ($cartItems as $cartItem) {
                $product = MenuItem::query()
                    ->with('user')
                    ->lockForUpdate()
                    ->find($cartItem->menu_item_id);

                if (! $product || ! $product->is_active || ! ($product->user?->hasSellerAccess() ?? false) || $product->user->status !== 'active') {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu produk di keranjang sudah tidak tersedia.',
                    ]);
                }

                if ($product->stock < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->name} tidak mencukupi.",
                    ]);
                }

                if ($sellerId !== null && $sellerId !== $product->user_id) {
                    throw ValidationException::withMessages([
                        'cart' => 'Satu pesanan hanya dapat berisi produk dari satu restoran.',
                    ]);
                }

                $sellerId = $product->user_id;
                $restaurantName = $product->user->sellerApplication?->shop_name ?? $product->user->name;
                $subtotal = $product->price * $cartItem->quantity;
                $total += $subtotal;
                $preparedItems[] = [
                    'cart_item' => $cartItem,
                    'product' => $product,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::query()->create([
                'user_id' => $customer->id,
                'seller_id' => $sellerId,
                'order_number' => 'TEMP-'.substr(Str::uuid()->toString(), 0, 8),
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'restaurant_name' => $restaurantName,
                'total_amount' => $total,
                'address' => $data['address'] ?? (isset($data['address_id'])
                    ? $customer->addresses()->findOrFail($data['address_id'])->address
                    : $customer->addresses()->where('is_default', true)->value('address')),
                'notes' => $data['notes'] ?? $data['note'] ?? null,
                'status' => 'pending',
            ]);
            $order->update([
                'order_number' => sprintf('DY-%s-%06d', now()->format('ymd'), $order->id),
            ]);

            foreach ($preparedItems as $preparedItem) {
                $product = $preparedItem['product'];
                $cartItem = $preparedItem['cart_item'];

                $order->items()->create([
                    'menu_item_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $preparedItem['subtotal'],
                ]);
                $product->decrement('stock', $cartItem->quantity);
            }

            $order->statusHistory()->create([
                'user_id' => $customer->id,
                'status' => 'pending',
            ]);
            $customer->cartItems()->delete();

            $seller = $order->seller()->first();
            if ($seller) {
                $seller->notify(new OrderStatusUpdated($order->order_number, 'pending', $order->id, 'Pesanan baru masuk'));
                (new PushNotificationService)->sendToUser($seller, [
                    'title' => 'Pesanan baru masuk',
                    'body' => 'Pesanan '.$order->order_number.' menunggu konfirmasi.',
                    'type' => 'new_order',
                    'data' => ['order_id' => (string) $order->id, 'order_number' => $order->order_number],
                ]);
            }

            return $order->load(['items', 'seller:id,name', 'driver:id,name']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $order,
        ], 201);
    }
}