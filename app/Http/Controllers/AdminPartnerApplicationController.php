<?php

namespace App\Http\Controllers;

use App\Models\DriverApplication;
use App\Models\SellerApplication;
use App\Services\PartnerApplicationReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminPartnerApplicationController extends Controller
{
    public function index(string $type): View
    {
        $model = $this->model($type);

        return view('admin.applications.index', [
            'type' => $type,
            'applications' => $model::query()->with('user:id,name,email,phone')->latest()->paginate(20),
            'application' => null,
        ]);
    }

    public function show(string $type, int $application): View
    {
        $record = $this->model($type)::query()->with(['user', 'approver:id,name'])->findOrFail($application);

        return view('admin.applications.index', [
            'type' => $type,
            'applications' => $this->model($type)::query()->with('user:id,name,email,phone')->latest()->paginate(20),
            'application' => $record,
        ]);
    }

    public function review(Request $request, string $type, int $application, PartnerApplicationReviewService $reviewer): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $reviewer->review($type, $application, $data['status'], $data['admin_note'] ?? null, $request->user());

        return redirect()->route('admin.applications.show', [$type, $application])
            ->with('success', 'Pengajuan berhasil ditinjau.');
    }

    public function document(Request $request, string $type, int $application, int $document)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $record = $this->model($type)::query()->findOrFail($application);
        $path = $record->document_paths[$document] ?? null;
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    private function model(string $type): string
    {
        abort_unless(in_array($type, ['seller', 'driver'], true), 404);

        return $type === 'seller' ? SellerApplication::class : DriverApplication::class;
    }
}