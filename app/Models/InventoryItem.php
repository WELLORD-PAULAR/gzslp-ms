<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    public const CATEGORIES = [
        'paper',
        'blank_mug',
        'blank_shirt',
        'tarpaulin_material',
        'ink',
        'other',
    ];

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit',
        'quantity_on_hand',
        'reorder_level',
        'is_active',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_supplies')
            ->withPivot('quantity_per_unit');
    }

    public function isLowStock(): bool
    {
        return (float) $this->quantity_on_hand <= (float) $this->reorder_level;
    }
}