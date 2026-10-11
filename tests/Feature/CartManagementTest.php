<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a customer can add products, accumulate quantities, update and remove cart items', function () {
    $customer = User::factory()->create();
    $product = Product::create([
        'name' => 'A4 Print',
        'price' => '12.50',
        'paper_size' => 'A4',
        'is_active' => true,
    ]);

    $this->actingAs($customer)
        ->post(route('cart.items.store'), [
            'item_type' => 'product',
            'item_id' => $product->id,
            'quantity' => 2,
        ])
        ->assertRedirect(route('cart'));

    $this->post(route('cart.items.store'), [
        'item_type' => 'product',
        'item_id' => $product->id,
        'quantity' => 3,
    ])->assertRedirect(route('cart'));

    $cart = Cart::where('user_id', $customer->id)->firstOrFail();
    $cartItem = $cart->items()->firstOrFail();
    expect($cartItem->quantity)->toBe(5);

    $this->get(route('cart'))
        ->assertOk()
        ->assertSee('A4 Print')
        ->assertSee('A4')
        ->assertSee('62.50')
        ->assertSee('>5</span>', false);

    $this->patch(route('cart.items.update', $cartItem), ['quantity' => 4])
        ->assertRedirect(route('cart'));
    expect($cartItem->fresh()->quantity)->toBe(4);

    $this->delete(route('cart.items.destroy', $cartItem))
        ->assertRedirect(route('cart'));
    $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
});

test('a customer can add a quote-only service and the cart marks the total as pending', function () {
    $customer = User::factory()->create();
    $product = Product::create(['name' => 'Basic Print', 'price' => '7.50', 'is_active' => true]);
    $service = Service::create([
        'name' => 'Custom Artwork',
        'starting_price' => null,
        'is_active' => true,
    ]);

    $this->actingAs($customer)
        ->post(route('cart.items.store'), [
            'item_type' => 'product',
            'item_id' => $product->id,
            'quantity' => 1,
        ])
        ->assertRedirect(route('cart'));

    $this->post(route('cart.items.store'), [
            'item_type' => 'service',
            'item_id' => $service->id,
            'quantity' => 1,
        ])
        ->assertRedirect(route('cart'));

    $this->get(route('cart'))
        ->assertOk()
        ->assertSee('Custom Artwork')
        ->assertSee('Quote required')
        ->assertSee('Final total requires a quote')
        ->assertSee('7.50');
});

test('cart items belong only to their customer', function () {
    $owner = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $product = Product::create(['name' => 'Print', 'price' => '5.00', 'is_active' => true]);
    $cart = $owner->cart()->create();
    $cartItem = $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->actingAs($otherCustomer)
        ->patch(route('cart.items.update', $cartItem), ['quantity' => 4])
        ->assertNotFound();

    expect($cartItem->fresh()->quantity)->toBe(1);
});

test('inactive items cannot be added and admins cannot use customer carts', function () {
    $customer = User::factory()->create();
    $inactiveProduct = Product::create(['name' => 'Hidden print', 'price' => '5.00', 'is_active' => false]);

    $this->actingAs($customer)
        ->post(route('cart.items.store'), [
            'item_type' => 'product',
            'item_id' => $inactiveProduct->id,
            'quantity' => 1,
        ])
        ->assertNotFound();

    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->actingAs($admin)->get(route('cart'))->assertForbidden();
});

test('cart quantities must stay within supported limits', function () {
    $customer = User::factory()->create();
    $product = Product::create(['name' => 'Print', 'price' => '5.00', 'is_active' => true]);

    $this->actingAs($customer)
        ->from(route('products.index'))
        ->post(route('cart.items.store'), [
            'item_type' => 'product',
            'item_id' => $product->id,
            'quantity' => 100,
        ])
        ->assertSessionHasErrors('quantity');
});