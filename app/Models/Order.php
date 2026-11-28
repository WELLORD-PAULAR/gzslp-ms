<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_QUOTE_REQUESTED = 'quote_requested';

    public const STATUS_QUOTE_SENT = 'quote_sent';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const ADMIN_STATUSES = [
        self::STATUS_QUOTE_SENT,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'user_id',
        'status',
        'subtotal',
        'quoted_total',
        'customer_accepted_at',
        'artwork_path',
        'customer_note',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'quoted_total' => 'decimal:2',
            'customer_accepted_at' => 'datetime',
            'inventory_consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * A final total exists once every order line has a price, or a quote has
     * been issued. Quote-requested orders return null until they are priced.
     */
    public function hasFinalTotal(): bool
    {
        return $this->current_total !== null;
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_QUOTE_REQUESTED,
            self::STATUS_QUOTE_SENT,
        ], true);
    }

    public function requiresQuote(): bool
    {
        if (in_array($this->status, [self::STATUS_QUOTE_REQUESTED, self::STATUS_QUOTE_SENT], true)
            || $this->quoted_total !== null) {
            return true;
        }

        return $this->relationLoaded('items')
            ? $this->items->contains(fn (OrderItem $item): bool => $item->unit_price === null)
            : $this->items()->whereNull('unit_price')->exists();
    }

    public function isAwaitingQuote(): bool
    {
        return $this->status === self::STATUS_QUOTE_REQUESTED;
    }

    public function hasQuote(): bool
    {
        return $this->quoted_total !== null;
    }

    public function allowedAdminStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_QUOTE_REQUESTED => [self::STATUS_QUOTE_SENT, self::STATUS_CANCELLED],
            self::STATUS_QUOTE_SENT => [self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
            default => [],
        };
    }

    public function getCurrentTotalAttribute(): ?string
    {
        if ($this->quoted_total !== null) {
            return $this->quoted_total;
        }

        $hasUnpricedItems = $this->relationLoaded('items')
            ? $this->items->contains(fn (OrderItem $item): bool => $item->unit_price === null)
            : $this->items()->whereNull('unit_price')->exists();

        if ($hasUnpricedItems) {
            return null;
        }

        return $this->subtotal;
    }
}