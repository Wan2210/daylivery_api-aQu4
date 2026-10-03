<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    private const STATUSES = [
        'pending',
        'menunggu',
        'confirmed',
        'preparing',
        'diproses',
        'ready',
        'assigned',
        'picked_up',
        'delivering',
        'diantar',
        'completed',
        'selesai',
        'cancelled',
        'dibatalkan',
    ];

    private const STATUS_TRANSITIONS = [
        'pending' => [
            'confirmed',
            'cancelled',
            'dibatalkan',
        ],

        'menunggu' => [
            'confirmed',
            'dibatalkan',
        ],

        'confirmed' => [
            'preparing',
            'diproses',
            'cancelled',
            'dibatalkan',
        ],

        'preparing' => [
            'ready',
            'cancelled',
            'dibatalkan',
        ],

        'diproses' => [
            'ready',
            'cancelled',
            'dibatalkan',
        ],

        'ready' => [
            'assigned',
            'cancelled',
            'dibatalkan',
        ],

        'assigned' => [
            'picked_up',
        ],

        'picked_up' => [
            'delivering',
            'diantar',
        ],

        'delivering' => [
            'completed',
            'selesai',
        ],

        'diantar' => [
            'completed',
            'selesai',
        ],

        'completed' => [],

        'selesai' => [],

        'cancelled' => [],

        'dibatalkan' => [],
    ];

    /**
     * Menampilkan semua order.
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'user:id,name,email,phone',
            'seller:id,name,email,phone',
            'driver:id,name,email,phone',
            'items.menuItem',
        ]);

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $request->validate([
                'status' => [
                    'string',
                    'in:'.implode(',', self::STATUSES),
                ],
            ]);

            $query->where('status', $request->status);
        }

        // Filter berdasarkan driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter berdasarkan customer
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query
            ->latest()
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar seluruh order berhasil diambil.',
            'data' => $orders,
        ]);
    }

    /**
     * Detail order.
     */
    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email,phone',
            'seller:id,name,email,phone',
            'driver:id,name,email,phone',
            'items.menuItem',
            'statusHistory.user:id,name,role',
        ]);

        return response()->json([
            'message' => 'Detail order berhasil diambil.',
            'data' => $order,
        ]);
    }

    /**
     * Admin mengubah status order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => [
                'required',
                'in:'.implode(',', self::STATUSES),
            ],
        ]);

        $result = DB::transaction(function () use ($request, $order): Order|JsonResponse {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $newStatus = $request->string('status')->toString();
            $currentStatus = $lockedOrder->status;
            $allowedStatuses = self::STATUS_TRANSITIONS[$currentStatus] ?? [];

            if (! in_array($newStatus, $allowedStatuses, true)) {
                return response()->json([
                    'message' => "Status {$currentStatus} tidak dapat diubah menjadi {$newStatus}.",
                    'current_status' => $currentStatus,
                    'allowed_statuses' => $allowedStatuses,
                ], 422);
            }

            $lockedOrder->update([
                'status' => $newStatus,
            ]);

            $lockedOrder->statusHistory()->create([
                'user_id' => $request->user()->id,
                'status' => $newStatus,
            ]);

            $customer = $lockedOrder->user()->first();
            if ($customer) {
                $customer->notify(new OrderStatusUpdated($lockedOrder->order_number, $newStatus, $lockedOrder->id, 'Status pesanan diubah admin'));
                (new PushNotificationService)->sendToUser($customer, [
                    'title' => 'Status pesanan diubah admin',
                    'body' => 'Pesanan '.$lockedOrder->order_number.' sekarang '.$newStatus.'.',
                    'type' => 'order_status_updated',
                    'data' => ['order_id' => (string) $lockedOrder->id, 'order_number' => $lockedOrder->order_number, 'status' => $newStatus],
                ]);
            }

            return $lockedOrder->fresh()->load([
                'user:id,name,email,phone',
                'seller:id,name,email,phone',
                'driver:id,name,email,phone',
                'items.menuItem',
                'statusHistory.user:id,name,role',
            ]);
        });

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json([
            'message' => 'Status order berhasil diperbarui.',
            'data' => $result,
        ]);
    }

    /**
     * Admin menetapkan driver ke order.
     */
    public function assignDriver(
        Request $request,
        Order $order
    ) {
        $request->validate([
            'driver_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $result = DB::transaction(function () use ($request, $order): Order|JsonResponse {
            $driver = User::query()
                ->lockForUpdate()
                ->find($request->integer('driver_id'));

            if (! $driver || ! $driver->hasDriverAccess()) {
                return response()->json([
                    'message' => 'User yang dipilih bukan driver.',
                ], 422);
            }

            if ($driver->status !== 'active' || $driver->is_blocked) {
                return response()->json([
                    'message' => 'Driver tidak aktif atau sedang diblokir.',
                    'status' => $driver->status,
                ], 422);
            }

            if ($driver->suspended_until?->isFuture()) {
                return response()->json([
                    'message' => 'Driver sedang disuspend.',
                    'suspended_until' => $driver->suspended_until,
                ], 422);
            }

            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status !== 'ready') {
                return response()->json([
                    'message' => 'Driver hanya dapat ditugaskan pada order yang berstatus ready.',
                    'status' => $lockedOrder->status,
                ], 422);
            }

            if ($lockedOrder->driver_id !== null) {
                return response()->json([
                    'message' => 'Order sudah memiliki driver.',
                    'driver_id' => $lockedOrder->driver_id,
                ], 422);
            }

            $lockedOrder->update([
                'driver_id' => $driver->id,
                'status' => 'assigned',
            ]);

            $lockedOrder->statusHistory()->create([
                'user_id' => $request->user()->id,
                'status' => 'assigned',
            ]);

            return $lockedOrder->fresh()->load([
                'user:id,name,email,phone',
                'seller:id,name,email,phone',
                'driver:id,name,email,phone',
                'items.menuItem',
                'statusHistory.user:id,name,role',
            ]);
        });

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json([
            'message' => 'Driver berhasil ditugaskan ke order.',
            'data' => $result,
        ]);
    }

    /**
     * Daftar driver yang tersedia.
     */
    public function availableDrivers()
    {
        $drivers = User::query()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'status',
                'rating',
                'warning_level',
                'warning_count',
            ])
            ->where(function ($query): void {
                $query->where('role', 'driver')
                    ->orWhereHas('driverApplication', fn ($applicationQuery) => $applicationQuery->where('status', 'approved'));
            })
            ->where('status', 'active')
            ->where('is_blocked', false)
            ->where(function ($query) {
                $query->whereNull('suspended_until')
                    ->orWhere('suspended_until', '<=', now());
            })
            ->orderByDesc('rating')
            ->get();

        return response()->json([
            'message' => 'Daftar driver tersedia berhasil diambil.',
            'data' => $drivers,
        ]);
    }
}
