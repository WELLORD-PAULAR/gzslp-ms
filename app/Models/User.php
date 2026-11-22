<?php

namespace App\Models;

use App\Services\Verification\VerificationCodeService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_EMPLOYEE = 'employee';

    public const ROLE_ADMIN = 'admin';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isEmployee();
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isPreviewingStorefront(): bool
    {
        return $this->isStaff()
            && app()->bound('session')
            && session()->get('viewing_storefront') === true;
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Laravel's verification hooks call this method; deliver our one-time code
     * instead of the framework's signed link notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        if (! $this->hasVerifiedEmail()) {
            app(VerificationCodeService::class)->send($this, VerificationCode::CHANNEL_EMAIL);
        }
    }
}
