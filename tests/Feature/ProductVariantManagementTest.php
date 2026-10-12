<?php

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a staff member can create a priced product option with a raw-supply recipe', function () {
    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();
    $product = Product::create([
        'name' => 'Graphic Zone Shirt',
        'category' => 'shirt',
        'price' => '25.00',
        'is_active' => true,
    ]);
    $supply = InventoryItem::create([
        'name' => 'Blank black shirt',
        'category' => 'blank_shirt',
        'unit' => 'piece',
        'quantity_on_hand' => '20.000',
        'reorder_level' => '5.000',
        'is_active' => true,
    ]);

    $this->actingAs($employee)
        ->get(route('admin.products.variants.index', $product))
        ->assertOk();

    $this->post(route('admin.products.variants.store', $product), [
        'name' => 'Large / Black',
        'sku' => 'GZ-SHIRT-L-BLK',
        'price' => '28.50',
        'options' => [
            'size' => 'Large',
            'color' => 'Black',
            'material' => 'Cotton',
        ],
        'supplies' => [$supply->id => '1.000'],
    ])->assertRedirect(route('admin.products.variants.index', $product));

    $variant = ProductVariant::where('sku', 'GZ-SHIRT-L-BLK')->firstOrFail();
    expect($variant->product_id)->toBe($product->id)
        ->and($variant->options)->toBe(['size' => 'Large', 'color' => 'Black', 'material' => 'Cotton'])
        ->and((float) $variant->supplies->first()->pivot->quantity_per_unit)->toBe(1.0);
});

test('customers add a selected variant and the variant price/options are kept in the order snapshot', function () {
    $customer = User::factory()->create();
    $product = Product::create([
        'name' => 'Custom mug',
        'category' => 'mug',
        'price' => '10.00',
        'is_active' => true,
    ]);
    $default = $product->variants()->firstOrFail();
    $variant = $product->variants()->create([
        'name' => '15 oz / Blue',
        'sku' => 'MUG-15-BLUE',
        'options' => ['capacity' => '15 oz', 'color' => 'Blue'],
        'price' => '14.75',
        'is_default' => false,
        'is_active' => true,
    ]);

    $this->actingAs($customer)
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('15 oz / Blue')
        ->assertSee('Blue');

    $this->post(route('cart.items.store'), [
        'item_type' => 'product',
        'item_id' => $product->id,
        'variant_id' => $variant->id,
        'quantity' => 2,
    ])->assertRedirect(route('cart'));

    $this->post(route('orders.store'))->assertRedirect(route('orders.show', 1));

    $orderItem = \App\Models\OrderItem::firstOrFail();
    expect($orderItem->product_variant_id)->toBe($variant->id)
        ->and($orderItem->variant_name)->toBe('15 oz / Blue')
        ->and($orderItem->variant_options)->toBe(['capacity' => '15 oz', 'color' => 'Blue'])
        ->and($orderItem->unit_price)->toBe('14.75')
        ->and($orderItem->quantity)->toBe(2)
        ->and($default->is_default)->toBeTrue();
});

test('a stale product variant edit is rejected', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $product = Product::create(['name' => 'Tarpaulin', 'category' => 'tarpaulin', 'price' => '30.00', 'is_active' => true]);
    $variant = $product->variants()->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.products.variants.update', [$product, $variant]), [
            'name' => '2m x 3m',
            'price' => '35.00',
            'options' => ['dimensions' => '2m x 3m', 'thickness' => '440 gsm'],
            'supplies' => [],
            'is_active' => '1',
            'lock_version' => 0,
        ])->assertRedirect(route('admin.products.variants.index', $product));

    $this->from(route('admin.products.variants.edit', [$product, $variant]))
        ->put(route('admin.products.variants.update', [$product, $variant]), [
            'name' => 'Stale size',
            'price' => '40.00',
            'options' => ['dimensions' => '3m x 4m'],
            'supplies' => [],
            'is_active' => '1',
            'lock_version' => 0,
        ])->assertSessionHasErrors('conflict');

    expect($variant->fresh()->name)->toBe('2m x 3m')
        ->and($product->fresh()->price)->toBe('35.00');
});