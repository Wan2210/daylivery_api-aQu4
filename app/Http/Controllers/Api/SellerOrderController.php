<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerOrderController extends Controller
{
    /**
     * Menampilkan pesanan yang memiliki menu milik seller.
     */
    public function index(Request $request)
    {
        $sellerId = $request->user()->id;

        $orders = Order::with([
            'user:id,name,email',
            'items.menuItem',
        ])
            ->where(function ($query) use ($sellerId): void {
                $query->where('seller_id', $sellerId)
                    ->orWhereHas('items.menuItem', fn ($itemQuery) => $itemQuery->where('user_id', $sellerId));
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pesanan seller berhasil diambil.',
            'data' => $orders,
        ]);
    }

    /**
     * Detail pesanan seller.
     */
    public function show(Request $request, Order $order)
    {
        $sellerId = $request->user()->id;

        $hasSellerItem = $order->seller_id === $sellerId || $order->items()
            ->whereHas('menuItem', function ($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })
            ->exists();

        if (! $hasSellerItem) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini tidak memiliki menu milik Anda.',
            ], 403);
        }

        $order->load([
            'user:id,name,email',
            'items.menuItem',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $order,
        ]);
    }

    /**
     * Seller mengubah status pesanan.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => [
                'required',
                'in:confirmed,preparing,ready,cancelled,dibatalkan',
            ],
        ]);

        return DB::transaction(function () use ($request, $order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $sellerId = $request->user()->id;
            $hasSellerItem = $lockedOrder->seller_id === $sellerId || $lockedOrder->items()
                ->whereHas('menuItem', fn ($query) => $query->where('user_id', $sellerId))
                ->exists();

            if (! $hasSellerItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke pesanan ini.',
                ], 403);
            }

            $currentStatus = $lockedOrder->status;
            $newStatus = $request->input('status');
            $allowedTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'menunggu' => ['confirmed', 'dibatalkan'],
                'confirmed' => ['preparing'],
                'preparing' => ['ready'],
            ];

            if (! in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
                return response()->json([
                    'success' => false,
                    'message' => "Status {$currentStatus} tidak dapat diubah menjadi {$newStatus}.",
                    'current_status' => $currentStatus,
                    'allowed_statuses' => $allowedTransitions[$currentStatus] ?? [],
                ], 422);
            }

            if (in_array($newStatus, ['cancelled', 'dibatalkan'], true)) {
                foreach ($lockedOrder->items as $item) {
                    if ($item->menu_item_id !== null) {
                        $item->menuItem()->lockForUpdate()->first()?->increment('stock', $item->quantity);
                    }
                }
            }

            $lockedOrder->update(['status' => $newStatus]);
            $lockedOrder->statusHistory()->create([
                'user_id' => $sellerId,
                'status' => $newStatus,
            ]);

            $customer = $lockedOrder->user()->first();
            if ($customer) {
                $customer->notify(new OrderStatusUpdated($lockedOrder->order_number, $newStatus, $lockedOrder->id, 'Status pesanan diperbarui'));
                (new PushNotificationService)->sendToUser($customer, [
                    'title' => 'Status pesanan diperbarui',
                    'body' => 'Pesanan '.$lockedOrder->order_number.' sekarang '.$newStatus.'.',
                    'type' => 'order_status_updated',
                    'data' => ['order_id' => (string) $lockedOrder->id, 'order_number' => $lockedOrder->order_number, 'status' => $newStatus],
                ]);
            }

            if ($newStatus === 'ready' && $lockedOrder->driver) {
                $lockedOrder->driver->notify(new OrderStatusUpdated($lockedOrder->order_number, 'ready', $lockedOrder->id, 'Order siap diantar'));
                (new PushNotificationService)->sendToUser($lockedOrder->driver, [
                    'title' => 'Order siap diantar',
                    'body' => 'Pesanan '.$lockedOrder->order_number.' siap untuk diantar.',
                    'type' => 'order_ready',
                    'data' => ['order_id' => (string) $lockedOrder->id, 'order_number' => $lockedOrder->order_number],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui.',
                'data' => $lockedOrder->fresh()->load([
                    'user:id,name,email',
                    'items.menuItem',
                    'statusHistory.user:id,name,role',
                ]),
            ]);
        });
    }
}
