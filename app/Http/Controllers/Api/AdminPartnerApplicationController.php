<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverApplication;
use App\Models\SellerApplication;
use App\Services\PartnerApplicationReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPartnerApplicationController extends Controller
{
    public function index(string $type): JsonResponse
    {
        $model = $type === 'seller' ? SellerApplication::class : DriverApplication::class;
        $applications = $model::query()->with(['user:id,name,email,phone', 'approver:id,name'])
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $applications]);
    }

    public function show(string $type, int $application): JsonResponse
    {
        $model = $type === 'seller' ? SellerApplication::class : DriverApplication::class;
        $record = $model::query()->with(['user', 'approver:id,name'])->findOrFail($application);

        return response()->json(['success' => true, 'data' => $record]);
    }

    public function review(Request $request, string $type, int $application, PartnerApplicationReviewService $reviewer): JsonResponse
    {
        abort_unless(in_array($type, ['seller', 'driver'], true), 404);
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $record = $reviewer->review($type, $application, $data['status'], $data['admin_note'] ?? null, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil ditinjau.',
            'data' => $record,
        ]);
    }
}