<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverApplication extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'phone', 'address', 'identity_number', 'license_number',
        'vehicle_type', 'vehicle_number', 'document_paths', 'status', 'admin_note', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return ['document_paths' => 'array', 'approved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}