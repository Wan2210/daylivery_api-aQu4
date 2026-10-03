<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['sometimes', 'in:customer'],
        ]);

        if (User::customerLimitReached()) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pelanggan sudah mencapai batas 2.000 akun.',
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $token = $user->createToken('daylivery-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil',
            'user' => $this->userData($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password) || $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('daylivery-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $this->userData($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $this->userData($request->user()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required_without_all:email', 'string', 'max:100'],
            'email' => [
                'required_without_all:name',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->fill($validated)->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => $this->userData($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        Auth::guard('sanctum')->forgetUser();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    private function userData(User $user): array
    {
        $sellerAccess = $user->hasSellerAccess();
        $driverAccess = $user->hasDriverAccess();
        $roles = [$user->role];

        if ($sellerAccess && ! in_array('seller', $roles, true)) {
            $roles[] = 'seller';
        }

        if ($driverAccess && ! in_array('driver', $roles, true)) {
            $roles[] = 'driver';
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'roles' => $roles,
            'status' => $user->status,
            'seller_access' => $sellerAccess,
            'seller_application_status' => $user->sellerApplication?->status,
            'driver_access' => $driverAccess,
            'driver_application_status' => $user->driverApplication?->status,
        ];
    }
}
