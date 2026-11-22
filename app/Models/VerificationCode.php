<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_SMS = 'sms';

    protected $fillable = [
        'user_id',
        'channel',
        'destination',
        'code_hash',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $hidden = [
        'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? true;
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
