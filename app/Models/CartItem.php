<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
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

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getNameAttribute(): ?string
    {
        if ($this->product) {
            return $this->productVariant && (! $this->productVariant->is_default || count($this->productVariant->options ?? []) > 0)
                ? $this->product->name.' — '.$this->productVariant->name
                : $this->product->name;
        }

        return $this->service?->name;
    }

    public function getUnitPriceAttribute(): ?string
    {
        return $this->productVariant?->price ?? $this->product?->price ?? $this->service?->starting_price;
    }

    public function getIsAvailableAttribute(): bool
    {
        if ($this->product) {
            return $this->product->is_active && ($this->productVariant?->is_active ?? false);
        }

        return (bool) ($this->service?->is_active ?? false);
    }
}