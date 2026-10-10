<?php

use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('products and services can be stored with catalog details', function () {
    $product = Product::create([
        'name' => 'A4 Matte Print',
        'description' => 'Full-color matte paper print.',
        'price' => 12.50,
        'paper_size' => 'A4',
        'paper_weight' => '150 gsm',
        'paper_finish' => 'Matte',
    ]);
    $service = Service::create([
        'name' => 'Custom Artwork Printing',
        'description' => 'Printing from customer-supplied artwork.',
        'starting_price' => 20,
    ]);

    expect($product->fresh()->price)->toBe('12.50')
        ->and($product->fresh()->paper_size)->toBe('A4')
        ->and($product->fresh()->paper_weight)->toBe('150 gsm')
        ->and($product->fresh()->paper_finish)->toBe('Matte')
        ->and($product->fresh()->is_active)->toBeTrue()
        ->and($service->fresh()->starting_price)->toBe('20.00')
        ->and($service->fresh()->is_active)->toBeTrue();
});