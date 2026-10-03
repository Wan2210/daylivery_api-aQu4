<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverOrderController extends Controller
{
    /**
     * Menampilkan order yang siap diantar.
     */
    public function available(Request $request)
    {
        $orders = Order::with([
            'user:id,name,email',
            'seller:id,name',
            'items.menuItem',
        ])
            ->where('status', 'ready')
            ->whereNull('driver_id')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar order yang tersedia berhasil diambil.',
            'data' => $orders,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $driverId = $request->user()->id;
        $visible = $order->driver_id === $driverId || ($order->driver_id === null && $order->status === 'ready');
        abort_unless($visible, 404);

        return response()->json([
            'success' => true,
            'data' => $order->load(['user:id,name,email,phone', 'seller:id,name', 'items.menuItem']),
        ]);
    }

    /**
     * Driver mengambil order.
     */
    public function accept(Request $request, Order $order)
    {
        $claimed = DB::transaction(function () use ($request, $order): bool {
            $claimed = Order::query()
                ->whereKey($order->getKey())
                ->where('status', 'ready')
                ->whereNull('driver_id')
                ->update([
                    'driver_id' => $request->user()->id,
                    'status' => 'assigned',
                ]);

            if ($claimed) {
                $order->statusHistory()->create([
                    'user_id' => $request->user()->id,
                    'status' => 'assigned',
                ]);

                $customer = $order->user()->first();
                if ($customer) {
                    $customer->notify(new OrderStatusUpdated($order->order_number, 'assigned', $order->id, 'Driver mengambil pesanan'));
                    (new PushNotificationService)->sendToUser($customer, [
                        'title' => 'Driver mengambil pesanan',
                        'body' => 'Driver sedang menuju restoran untuk pesanan '.$order->order_number.'.',
                        'type' => 'driver_assigned',
                        'data' => ['order_id' => (string) $order->id, 'order_number' => $order->order_number],
                    ]);
                }

                $seller = $order->seller()->first();
                if ($seller) {
                    $seller->notify(new OrderStatusUpdated($order->order_number, 'assigned', $order->id, 'Driver ditugaskan'));
                    (new PushNotificationService)->sendToUser($seller, [
                        'title' => 'Driver ditugaskan',
                        'body' => 'Driver siap mengambil pesanan '.$order->order_number.'.',
                        'type' => 'driver_assigned',
                        'data' => ['order_id' => (string) $order->id, 'order_number' => $order->order_number],
                    ]);
                }
            }

            return (bool) $claimed;
        });

        if (! $claimed) {
            $order->refresh();

            if ($order->status !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order belum siap untuk ditugaskan.',
                    'status' => $order->status,
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Order sudah diambil oleh driver lain.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diterima. Ambil pesanan saat sudah tiba di seller.',
            'data' => $order->fresh()->load([
                'user:id,name,email',
                'seller:id,name',
                'items.menuItem',
            ]),
        ]);
    }

    /**
     * Driver memperbarui status pengiriman.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => [
                'required',
                'in:picked_up,delivering,completed',
            ],
        ]);

        return DB::transaction(function () use ($request, $order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->driver_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda bukan driver yang menangani order ini.',
                ], 403);
            }

            $currentStatus = $lockedOrder->status;
            $newStatus = $request->input('status');
            $allowedTransitions = [
                'assigned' => ['picked_up'],
                'picked_up' => ['delivering'],
                'delivering' => ['completed'],
            ];

            if (! in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
                return response()->json([
                    'success' => false,
                    'message' => "Status {$currentStatus} tidak dapat diubah menjadi {$newStatus}.",
                    'current_status' => $currentStatus,
                    'allowed_statuses' => $allowedTransitions[$currentStatus] ?? [],
                ], 422);
            }

            $lockedOrder->update(['status' => $newStatus]);
            $lockedOrder->statusHistory()->create([
                'user_id' => $request->user()->id,
                'status' => $newStatus,
            ]);

            $customer = $lockedOrder->user()->first();
            if ($customer) {
                $customer->notify(new OrderStatusUpdated($lockedOrder->order_number, $newStatus, $lockedOrder->id, 'Status pengiriman diperbarui'));
                (new PushNotificationService)->sendToUser($customer, [
                    'title' => 'Status pengiriman diperbarui',
                    'body' => 'Pesanan '.$lockedOrder->order_number.' sekarang '.$newStatus.'.',
                    'type' => 'driver_status_updated',
                    'data' => ['order_id' => (string) $lockedOrder->id, 'order_number' => $lockedOrder->order_number, 'status' => $newStatus],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pengiriman berhasil diperbarui.',
                'data' => $lockedOrder->fresh()->load([
                    'user:id,name,email',
                    'seller:id,name',
                    'items.menuItem',
                    'statusHistory.user:id,name,role',
                ]),
            ]);
        });
    }

    /**
     * Daftar order yang sedang ditangani driver.
     */
    public function myOrders(Request $request)
    {
        $orders = Order::with([
            'user:id,name,email',
            'seller:id,name',
            'items.menuItem',
        ])
            ->where('driver_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Order driver berhasil diambil.',
            'data' => $orders,
        ]);
    }
}
