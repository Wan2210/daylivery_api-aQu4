<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DriverTrackingController extends Controller
{
    /**
     * Driver mengirim lokasi GPS terbaru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'battery_level' => ['nullable', 'integer', 'between:0,100'],
        ]);

        $driver = $request->user();

        $location = DriverLocation::create([
            'user_id' => $driver->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy' => $validated['accuracy'] ?? null,
            'speed_kmh' => $validated['speed_kmh'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'battery_level' => $validated['battery_level'] ?? null,
            'is_active' => true,

            // Waktu ditentukan server
            'recorded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi driver berhasil diperbarui.',
            'data' => $location,
        ]);
    }

    /**
     * Mengambil lokasi terakhir driver sendiri.
     */
    public function latest(Request $request)
    {
        $location = DriverLocation::query()
            ->where('user_id', $request->user()->id)
            ->latest('recorded_at')
            ->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data lokasi driver.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lokasi terakhir berhasil diambil.',
            'data' => $location,
        ]);
    }

    /**
     * Riwayat lokasi driver.
     */
    public function history(Request $request)
    {
        $limit = max(
            1,
            min((int) $request->query('limit', 20), 100)
        );

        $locations = DriverLocation::query()
            ->where('user_id', $request->user()->id)
            ->latest('recorded_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat lokasi berhasil diambil.',
            'data' => $locations,
        ]);
    }

    /**
     * Admin melihat lokasi terbaru semua driver aktif.
     */
    public function adminIndex(Request $request)
    {
        $drivers = User::query()
            ->where('role', 'driver')
            ->where('status', 'active')
            ->with([
                'driverLocations' => function ($query) {
                    $query
                        ->latest('recorded_at')
                        ->limit(1);
                },
            ])
            ->paginate(50);

        $payload = $drivers->through(function ($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->name,
                'email' => $driver->email,
                'status' => $driver->status,
                'last_location' => $driver->driverLocations->first(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Lokasi driver aktif berhasil diambil.',
            'data' => $payload,
        ]);
    }

    /**
     * Customer/driver/admin melihat tracking sebuah order.
     */
    public function orderTracking(Request $request, Order $order)
    {
        $user = $request->user();

        $canAccess =
            (int) $user->id === (int) $order->user_id ||
            (int) $user->id === (int) $order->driver_id ||
            $user->role === 'admin';

        abort_unless(
            $canAccess,
            403,
            'Anda tidak memiliki akses ke tracking order ini.'
        );

        // Order belum mempunyai driver
        if (!$order->driver_id) {
            return response()->json([
                'success' => true,
                'message' => 'Driver belum ditugaskan.',
                'data' => [
                    'order' => $order->load([
                        'user:id,name,email',
                        'seller:id,name',
                    ]),
                    'driver' => null,
                    'driver_location' => null,
                    'tracking_active' => false,
                ],
            ]);
        }

        // Ambil lokasi terbaru driver
        $location = DriverLocation::query()
            ->where('user_id', $order->driver_id)
            ->latest('recorded_at')
            ->first();

        $order->load([
            'user:id,name,email',
            'driver:id,name,phone',
            'seller:id,name',
        ]);

        /*
         * Tracking dianggap aktif jika order masih
         * dalam proses pengantaran.
         *
         * Sesuaikan nama status dengan sistem Daylivery.
         */
        $trackingStatuses = [
            'assigned',
            'picked_up',
            'on_delivery',
            'delivering',
        ];

        $trackingActive = in_array(
            $order->status,
            $trackingStatuses,
            true
        );

        return response()->json([
            'success' => true,
            'message' => 'Tracking order berhasil diambil.',
            'data' => [
                'order' => $order,
                'driver_location' => $location,
                'tracking_active' => $trackingActive,
            ],
        ]);
    }
}