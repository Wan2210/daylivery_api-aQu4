<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDriver
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== 'active' || ! $user->hasDriverAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya dapat diakses oleh akun driver aktif.',
            ], 403);
        }

        return $next($request);
    }
}