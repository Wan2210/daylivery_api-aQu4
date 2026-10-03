<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Akun berhasil dibuat.',
            'user' => $this->publicUser($user),
            'token' => $user->createToken('mobile')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'max:72'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password) || $user->role !== 'customer' || $user->status !== 'active') {
            return response()->json(['message' => 'Email atau kata sandi tidak valid.'], 401);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $this->publicUser($user),
            'token' => $user->createToken('mobile')->plainTextToken,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->publicUser($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        Auth::guard('sanctum')->forgetUser();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    private function publicUser(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'role', 'status']);
    }
}
