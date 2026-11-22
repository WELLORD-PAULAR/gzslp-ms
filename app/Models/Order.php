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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function succeededPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('status', Payment::STATUS_SUCCEEDED);
    }

    /**
     * Settled payments from the loaded relation when available, so list views
     * can show payment state without one query per order.
     *
     * @return \Illuminate\Support\Collection<int, Payment>
     */
    private function settledPayments()
    {
        return $this->relationLoaded('payments')
            ? $this->payments->where('status', Payment::STATUS_SUCCEEDED)
            : $this->succeededPayments()->get();
    }

    public function hasSucceededPayments(): bool
    {
        return $this->relationLoaded('payments')
            ? $this->payments->contains(fn (Payment $payment): bool => $payment->isSuccessful())
            : $this->succeededPayments()->exists();
    }

    public function paidAmountCents(): int
    {
        return (int) round($this->settledPayments()->sum(
            fn (Payment $payment): float => (float) $payment->amount,
        ) * 100);
    }

    public function amountDueCents(): ?int
    {
        $total = $this->current_total;

        if ($total === null) {
            return null;
        }

        return max(0, (int) round((float) $total * 100) - $this->paidAmountCents());
    }

    public function amountDue(): ?string
    {
        $cents = $this->amountDueCents();

        return $cents === null ? null : number_format($cents / 100, 2, '.', '');
    }

    /**
     * A quote is only chargeable once the customer has accepted it, which
     * moves the order into processing. A quote_sent order has a quoted total
     * but must not be paid before that acceptance.
     */
    public function hasFinalTotal(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
        ], true) && $this->current_total !== null;
    }

    public function needsPayment(): bool
    {
        return $this->hasFinalTotal() && ($this->amountDueCents() ?? 0) > 0;
    }

    public function isPaid(): bool
    {
        return $this->hasFinalTotal() && ! $this->needsPayment();
    }

    public function canBeCancelledByCustomer(): bool
    {
        // A paid order needs a refund decision before stock is returned.
        if ($this->hasSucceededPayments()) {
            return false;
        }

        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_QUOTE_REQUESTED,
            self::STATUS_QUOTE_SENT,
        ], true);
    }

    public function allowedAdminStatuses(): array
    {
        $cancellable = $this->hasSucceededPayments() ? [] : [self::STATUS_CANCELLED];

        return match ($this->status) {
            self::STATUS_PENDING => array_merge([self::STATUS_PROCESSING], $cancellable),
            self::STATUS_QUOTE_REQUESTED => array_merge([self::STATUS_QUOTE_SENT], $cancellable),
            self::STATUS_QUOTE_SENT => $cancellable,
            self::STATUS_PROCESSING => array_merge([self::STATUS_COMPLETED], $cancellable),
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