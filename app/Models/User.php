<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    public const MAX_CUSTOMERS = 2000;
    public const MAX_DRIVERS = 15;

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'rating',
        'warning_level',
        'warning_count',
        'warning_note',
        'suspended_until',
        'is_blocked',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rating' => 'decimal:2',
            'warning_level' => 'integer',
            'warning_count' => 'integer',
            'suspended_until' => 'datetime',
            'is_blocked' => 'boolean',
        ];
    }

    public static function customerLimitReached(): bool
    {
        return static::query()->where('role', 'customer')->count() >= self::MAX_CUSTOMERS;
    }

    public static function driverLimitReached(): bool
    {
        return static::query()
            ->where(function ($query): void {
                $query->where('role', 'driver')
                    ->orWhereHas('driverApplication', fn ($applicationQuery) => $applicationQuery->where('status', 'approved'));
            })
            ->count() >= self::MAX_DRIVERS;
    }

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function driverLocations(): HasMany
    {
        return $this->hasMany(DriverLocation::class);
    }

    public function pushTokens(): HasMany
    {
        return $this->hasMany(UserPushToken::class);
    }

    public function sellerApplication(): HasOne
    {
        return $this->hasOne(SellerApplication::class);
    }

    public function driverApplication(): HasOne
    {
        return $this->hasOne(DriverApplication::class);
    }

    public function hasSellerAccess(): bool
    {
        return ($this->role === 'seller' && $this->status === 'active')
            || $this->sellerApplication()->where('status', 'approved')->exists();
    }

    public function hasDriverAccess(): bool
    {
        return ($this->role === 'driver' && $this->status === 'active')
            || $this->driverApplication()->where('status', 'approved')->exists();
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
