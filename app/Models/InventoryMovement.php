<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    public const TYPE_RECEIVED = 'received';

    public const TYPE_USED = 'used';

    public const TYPE_STOCKTAKE = 'stocktake';

    public const TYPE_RETURNED = 'returned';

    public $timestamps = false;

    protected $fillable = [
        'inventory_item_id',
        'supplier_id',
        'user_id',
        'order_id',
        'type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'decimal:3',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'created_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}