<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Daftar semua user.
     */
    public function index(Request $request)
    {
        $query = User::query()
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
                'suspended_until',
                'is_blocked',
                'created_at',
            ]);

        // Filter berdasarkan role
        if ($request->filled('role')) {
            $request->validate([
                'role' => [
                    'string',
                    'in:customer,seller,driver,admin',
                ],
            ]);

            $role = $request->string('role')->toString();
            $query->where(function ($builder) use ($role): void {
                $builder->where('role', $role);

                if ($role === 'seller') {
                    $builder->orWhereHas('sellerApplication', fn ($application) => $application->where('status', 'approved'));
                } elseif ($role === 'driver') {
                    $builder->orWhereHas('driverApplication', fn ($application) => $application->where('status', 'approved'));
                }
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter user yang diblokir
        if ($request->has('is_blocked')) {
            $query->where(
                'is_blocked',
                filter_var($request->is_blocked, FILTER_VALIDATE_BOOLEAN)
            );
        }

        $users = $query
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Daftar user berhasil diambil.',
            'data' => $users,
        ]);
    }

    /**
     * Detail user.
     */
    public function show(User $user)
    {
        $user->load([
            'driverProfile',
            'addresses',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail user berhasil diambil.',
            'data' => $user,
        ]);
    }

    /**
     * Blokir user.
     */
    public function block(User $user)
    {
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Akun admin tidak dapat diblokir melalui endpoint ini.',
            ], 422);
        }

        $user->update([
            'is_blocked' => true,
            'status' => 'blocked',
        ]);

        return response()->json([
            'message' => 'User berhasil diblokir.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Buka blokir user.
     */
    public function unblock(User $user)
    {
        $user->update([
            'is_blocked' => false,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'User berhasil dibuka blokirnya.',
            'data' => $user->fresh(),
        ]);
    }
    /**
 * Suspend user.
 */
public function suspend(Request $request, User $user)
{
    if ($user->role === 'admin') {
        return response()->json([
            'message' => 'Akun admin tidak dapat disuspend melalui endpoint ini.',
        ], 422);
    }

    $request->validate([
        'hours' => [
            'required',
            'integer',
            'min:1',
            'max:720',
        ],
    ]);

    $suspendedUntil = now()->addHours($request->hours);

    $user->update([
        'suspended_until' => $suspendedUntil,
        'status' => 'suspended',
    ]);

    return response()->json([
        'message' => 'User berhasil disuspend.',
        'data' => $user->fresh(),
    ]);
}

/**
 * Unsuspend user.
 */
public function unsuspend(User $user)
{
    $user->update([
        'suspended_until' => null,
        'status' => 'active',
    ]);

    return response()->json([
        'message' => 'Suspend user berhasil dicabut.',
        'data' => $user->fresh(),
    ]);
}
}