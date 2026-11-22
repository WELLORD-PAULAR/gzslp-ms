<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'order_id',
        'user_id',
        'gateway',
        'method',
        'amount',
        'currency',
        'status',
        'reference',
        'failure_reason',
        'gateway_response',
    ];

    protected $hidden = [
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function formattedAmount(): string
    {
        return number_format((float) $this->amount, 2);
    }
}
