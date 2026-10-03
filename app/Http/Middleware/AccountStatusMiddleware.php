<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountStatusMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif.',
                'status' => $user->status,
            ], 403);
        }

        // Akun diblokir
        if ($user->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah diblokir.',
                'status' => 'blocked',
            ], 403);
        }

        // Akun sedang ditangguhkan
        if (
            $user->suspended_until &&
            $user->suspended_until->isFuture()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang ditangguhkan.',
                'status' => 'suspended',
                'suspended_until' => $user->suspended_until,
            ], 403);
        }

        return $next($request);
    }
}