<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'service_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getNameAttribute(): ?string
    {
        return $this->product?->name ?? $this->service?->name;
    }

    public function getUnitPriceAttribute(): ?string
    {
        return $this->product?->price ?? $this->service?->starting_price;
    }

    public function getIsAvailableAttribute(): bool
    {
        return (bool) ($this->product?->is_active ?? $this->service?->is_active ?? false);
    }
}