<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'starting_price',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starting_price' => 'decimal:2',
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }
}