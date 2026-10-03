<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverApplicationRequest;
use App\Http\Requests\StoreSellerApplicationRequest;
use App\Models\DriverApplication;
use App\Models\SellerApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerApplicationController extends Controller
{
    public function seller(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $request->user()->sellerApplication]);
    }

    public function submitSeller(StoreSellerApplicationRequest $request): JsonResponse
    {
        $user = $request->user();
        $application = $user->sellerApplication;

        if ($application?->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Akun ini sudah disetujui sebagai seller.'], 409);
        }

        $application ??= new SellerApplication(['user_id' => $user->id]);
        $application->fill($request->safe()->except('documents'));
        $application->status = 'pending';
        $application->admin_note = null;
        $application->approved_by = null;
        $application->approved_at = null;
        $application->save();

        if ($request->hasFile('documents')) {
            $application->document_paths = $this->storeDocuments($request, "partner-applications/sellers/{$application->id}");
            $application->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan seller berhasil dikirim.',
            'data' => $application->fresh(),
        ], 201);
    }

    public function driver(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $request->user()->driverApplication]);
    }

    public function submitDriver(StoreDriverApplicationRequest $request): JsonResponse
    {
        $user = $request->user();
        $application = $user->driverApplication;

        if ($application?->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Akun ini sudah disetujui sebagai driver.'], 409);
        }

        $application ??= new DriverApplication(['user_id' => $user->id]);
        $application->fill($request->safe()->except('documents'));
        $application->status = 'pending';
        $application->admin_note = null;
        $application->approved_by = null;
        $application->approved_at = null;
        $application->save();

        if ($request->hasFile('documents')) {
            $application->document_paths = $this->storeDocuments($request, "partner-applications/drivers/{$application->id}");
            $application->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan driver berhasil dikirim.',
            'data' => $application->fresh(),
        ], 201);
    }

    private function storeDocuments(Request $request, string $directory): array
    {
        return collect($request->file('documents', []))
            ->map(fn ($file): string => $file->store($directory, 'local'))
            ->all();
    }
}