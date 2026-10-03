<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $user = $request->user();
        $isDefault = $data['is_default'] ?? ! $user->addresses()->exists();

        $address = DB::transaction(function () use ($data, $user, $isDefault): Address {
            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                ...$data,
                'is_default' => $isDefault,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan',
            'data' => $address,
        ], 201);
    }

    public function show(Request $request, int $address): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($address);

        return response()->json(['success' => true, 'data' => $address]);
    }

    public function update(Request $request, int $address): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($address);
        $data = $request->validate($this->rules(false));

        DB::transaction(function () use ($data, $user, $address): void {
            if ($data['is_default'] ?? false) {
                $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }

            $address->fill($data)->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui',
            'data' => $address->refresh(),
        ]);
    }

    public function destroy(Request $request, int $address): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($address);

        DB::transaction(function () use ($address, $user): void {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $user->addresses()->latest('id')->first()?->update(['is_default' => true]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Alamat berhasil dihapus']);
    }

    private function rules(bool $creating = true): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return [
            'label' => ['sometimes', 'nullable', 'string', 'max:50'],
            'recipient_name' => [$required, 'string', 'max:100'],
            'phone' => [$required, 'string', 'max:30'],
            'address' => [$required, 'string', 'max:500'],
            'city' => [$required, 'string', 'max:100'],
            'province' => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
