<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'product_id',
        'service_id',
        'item_name',
        'item_description',
        'unit_price',
        'quantity',
        'paper_size',
        'paper_weight',
        'paper_finish',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getLineTotalAttribute(): ?string
    {
        if ($this->unit_price === null) {
            return null;
        }

        return number_format(((int) round((float) $this->unit_price * 100) * $this->quantity) / 100, 2, '.', '');
    }
}