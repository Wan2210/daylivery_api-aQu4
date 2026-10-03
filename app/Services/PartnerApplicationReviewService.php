<?php

namespace App\Services;

use App\Models\DriverApplication;
use App\Models\SellerApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartnerApplicationReviewService
{
    public function review(string $type, int $id, string $status, ?string $note, User $admin): Model
    {
        return DB::transaction(function () use ($type, $id, $status, $note, $admin): Model {
            $model = match ($type) {
                'seller' => SellerApplication::class,
                'driver' => DriverApplication::class,
                default => throw new \InvalidArgumentException('Unknown partner application type.'),
            };
            $application = $model::query()->with('user')->lockForUpdate()->findOrFail($id);

            if ($application->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pengajuan pending yang dapat ditinjau.',
                ]);
            }

            if ($type === 'driver' && $status === 'approved' && User::driverLimitReached()) {
                throw ValidationException::withMessages([
                    'status' => 'Kapasitas driver aktif sudah terpenuhi.',
                ]);
            }

            $application->update([
                'status' => $status,
                'admin_note' => $note,
                'approved_by' => $status === 'approved' ? $admin->id : null,
                'approved_at' => $status === 'approved' ? now() : null,
            ]);

            return $application->fresh(['user', 'approver']);
        });
    }
}