<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'options',
        'price',
        'is_default',
        'is_active',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplies(): BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class, 'product_variant_supplies')
            ->withPivot('quantity_per_unit');
    }

    public function availableQuantity(): ?int
    {
        $this->loadMissing('supplies');

        if ($this->supplies->isEmpty()) {
            return null;
        }

        $available = null;

        foreach ($this->supplies as $supply) {
            if (! $supply->is_active) {
                return 0;
            }

            $quantityPerUnitMilli = (int) round((float) $supply->pivot->quantity_per_unit * 1000);
            if ($quantityPerUnitMilli <= 0) {
                return 0;
            }

            $quantityOnHandMilli = (int) round((float) $supply->quantity_on_hand * 1000);
            $supplyUnits = intdiv(max(0, $quantityOnHandMilli), $quantityPerUnitMilli);
            $available = $available === null ? $supplyUnits : min($available, $supplyUnits);
        }

        return $available;
    }
}