<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const CATEGORIES = ['paper', 'mug', 'shirt', 'tarpaulin', 'other'];

    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'paper_size',
        'paper_weight',
        'paper_finish',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Product $product): void {
            $product->variants()->create([
                'name' => 'Standard',
                'options' => [],
                'price' => $product->price,
                'is_default' => true,
                'is_active' => true,
            ]);
        });
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
