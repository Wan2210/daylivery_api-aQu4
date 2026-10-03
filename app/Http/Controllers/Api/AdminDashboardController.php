<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerApplication;
use App\Models\DriverApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    private const CANCELLED_STATUSES = ['dibatalkan', 'cancelled', 'canceled'];

    public function index(): JsonResponse
    {
        $today = now()->toDateString();
        $todayOrders = Order::query()->whereDate('created_at', $today);
        $statusGroups = [
            'pending' => ['menunggu', 'pending'],
            'confirmed' => ['confirmed'],
            'preparing' => ['diproses', 'preparing'],
            'ready' => ['ready'],
            'assigned' => ['assigned'],
            'picked_up' => ['picked_up'],
            'delivering' => ['diantar', 'delivering'],
            'completed' => ['selesai', 'completed'],
            'cancelled' => self::CANCELLED_STATUSES,
        ];
        $statuses = collect($statusGroups)->mapWithKeys(fn (array $values, string $key): array => [
            $key => Order::query()->whereIn('status', $values)->count(),
        ]);
        $weeklyRevenue = collect(range(6, 0))->map(function (int $daysAgo): array {
            $day = now()->subDays($daysAgo);

            return [
                'date' => $day->toDateString(),
                'amount' => Order::query()
                    ->whereDate('created_at', $day->toDateString())
                    ->whereNotIn('status', self::CANCELLED_STATUSES)
                    ->sum('total_amount'),
            ];
        });
        $totalRevenue = Order::query()
            ->whereNotIn('status', self::CANCELLED_STATUSES)
            ->sum('total_amount');
        $todayRevenue = (clone $todayOrders)
            ->whereNotIn('status', self::CANCELLED_STATUSES)
            ->sum('total_amount');
        $activeOrders = Order::query()->whereIn('status', [
            'menunggu', 'pending', 'confirmed', 'diproses', 'preparing', 'ready',
            'assigned', 'picked_up', 'diantar', 'delivering',
        ])->count();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard admin berhasil diambil.',
            'data' => [
                'users' => [
                    'total' => User::query()->count(),
                    'customers' => User::query()->where('role', 'customer')->count(),
                    'sellers' => User::query()->where(function ($query): void {
                        $query->where('role', 'seller')
                            ->orWhereHas('sellerApplication', fn ($application) => $application->where('status', 'approved'));
                    })->count(),
                    'drivers' => User::query()->where(function ($query): void {
                        $query->where('role', 'driver')
                            ->orWhereHas('driverApplication', fn ($application) => $application->where('status', 'approved'));
                    })->count(),
                    'admins' => User::query()->where('role', 'admin')->count(),
                    'blocked' => User::query()->where('is_blocked', true)->count(),
                    'pending_seller_applications' => SellerApplication::query()->where('status', 'pending')->count(),
                    'pending_driver_applications' => DriverApplication::query()->where('status', 'pending')->count(),
                ],
                'orders' => [
                    'total' => Order::query()->count(),
                    'today' => (clone $todayOrders)->count(),
                    ...$statuses->all(),
                ],
                'revenue' => [
                    'total' => (int) $totalRevenue,
                    'today' => (int) $todayRevenue,
                ],
                'stats' => [
                    'orders_today' => (clone $todayOrders)->count(),
                    'revenue_today' => (int) $todayRevenue,
                    'active_orders' => $activeOrders,
                    'completed_today' => Order::query()
                        ->whereDate('created_at', $today)
                        ->whereIn('status', ['selesai', 'completed'])
                        ->count(),
                ],
                'statuses' => $statuses,
                'weekly_revenue' => $weeklyRevenue,
                'recent_orders' => Order::query()->with('items')->latest()->limit(10)->get(),
            ],
        ]);
    }
}