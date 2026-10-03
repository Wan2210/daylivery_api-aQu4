<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminModerationController extends Controller
{
    /**
     * Daftar seller.
     */
    public function sellers()
    {
        $sellers = User::query()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'role',
                'status',
                'rating',
                'warning_level',
                'warning_count',
                'warning_note',
                'suspended_until',
                'is_blocked',
                'created_at',
            ])
            ->where(function ($query): void {
                $query->where('role', 'seller')
                    ->orWhereHas('sellerApplication', fn ($application) => $application->where('status', 'approved'));
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar seller berhasil diambil.',
            'data' => $sellers,
        ]);
    }

    /**
     * Daftar driver.
     */
    public function drivers()
    {
        $drivers = User::query()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'role',
                'status',
                'rating',
                'warning_level',
                'warning_count',
                'warning_note',
                'suspended_until',
                'is_blocked',
                'created_at',
            ])
            ->where(function ($query): void {
                $query->where('role', 'driver')
                    ->orWhereHas('driverApplication', fn ($application) => $application->where('status', 'approved'));
            })
            ->with(['driverProfile', 'driverApplication'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar driver berhasil diambil.',
            'data' => $drivers,
        ]);
    }

    /**
     * Berikan warning kepada seller/driver.
     */
    public function warning(Request $request, User $user)
    {
        $request->validate([
            'note' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        if (! $user->hasSellerAccess() && ! $user->hasDriverAccess()) {
            return response()->json([
                'message' => 'Warning hanya dapat diberikan kepada seller atau driver.',
            ], 422);
        }

        $newWarningCount = $user->warning_count + 1;

        $warningLevel = match (true) {
            $newWarningCount >= 5 => 3,
            $newWarningCount >= 3 => 2,
            $newWarningCount >= 1 => 1,
            default => 0,
        };

        $user->update([
            'warning_count' => $newWarningCount,
            'warning_level' => $warningLevel,
            'warning_note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Warning berhasil diberikan.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Reset warning.
     */
    public function resetWarning(User $user)
    {
        if (! $user->hasSellerAccess() && ! $user->hasDriverAccess()) {
            return response()->json([
                'message' => 'User bukan seller atau driver.',
            ], 422);
        }

        $user->update([
            'warning_count' => 0,
            'warning_level' => 0,
            'warning_note' => null,
        ]);

        return response()->json([
            'message' => 'Warning berhasil direset.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Riwayat status seller/driver secara ringkas.
     */
    public function show(User $user)
    {
        if (! $user->hasSellerAccess() && ! $user->hasDriverAccess()) {
            return response()->json([
                'message' => 'User bukan seller atau driver.',
            ], 422);
        }

        $user->load([
            'driverProfile',
            'menuItems',
        ]);

        return response()->json([
            'message' => 'Data user berhasil diambil.',
            'data' => $user,
        ]);
    }
}