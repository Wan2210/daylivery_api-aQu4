<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data' => $notification->fresh(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi sudah dibaca.',
        ]);
    }

    public function registerToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
            'platform' => ['nullable', 'in:android,ios,web'],
            'device_name' => ['nullable', 'string', 'max:150'],
        ]);

        $token = $request->user()->pushTokens()
            ->where('token', $data['token'])
            ->first();

        if ($token) {
            $token->update([
                'platform' => $data['platform'] ?? $token->platform,
                'device_name' => $data['device_name'] ?? $token->device_name,
                'last_used_at' => now(),
                'is_active' => true,
            ]);
        } else {
            $request->user()->pushTokens()->create([
                'token' => $data['token'],
                'platform' => $data['platform'] ?? 'web',
                'device_name' => $data['device_name'] ?? null,
                'last_used_at' => now(),
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token push berhasil didaftarkan.',
        ]);
    }

    public function removeToken(Request $request, string $token): JsonResponse
    {
        UserPushToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $token)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token push berhasil dihapus.',
        ]);
    }
}
