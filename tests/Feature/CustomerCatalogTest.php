<?php

use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customers can search and filter active products and visit detail pages', function () {
    $customer = User::factory()->create();
    $matte = Product::create([
        'name' => 'A4 Matte Card',
        'description' => 'Thick matte card stock.',
        'price' => '12.00',
        'paper_size' => 'A4',
        'paper_finish' => 'Matte',
        'is_active' => true,
    ]);
    Product::create(['name' => 'A5 Gloss Card', 'price' => '10.00', 'paper_size' => 'A5', 'paper_finish' => 'Gloss', 'is_active' => true]);
    Product::create(['name' => 'Hidden A4 Matte', 'price' => '8.00', 'paper_size' => 'A4', 'paper_finish' => 'Matte', 'is_active' => false]);

    $this->actingAs($customer)
        ->get(route('products.index', ['search' => 'Card', 'paper_size' => 'A4', 'paper_finish' => 'Matte']))
        ->assertOk()
        ->assertSee('A4 Matte Card')
        ->assertDontSee('A5 Gloss Card')
        ->assertDontSee('Hidden A4 Matte');

    $this->get(route('products.show', $matte))
        ->assertOk()
        ->assertSee('Thick matte card stock.')
        ->assertSee('name="item_type"', false)
        ->assertSee('value="product"', false)
        ->assertSee('Add to cart');
});

test('customers can search active services and visit service details', function () {
    $customer = User::factory()->create();
    $service = Service::create([
        'name' => 'Custom poster printing',
        'description' => 'Send artwork for a custom poster.',
        'starting_price' => null,
        'is_active' => true,
    ]);
    Service::create(['name' => 'Hidden lamination', 'is_active' => false]);

    $this->actingAs($customer)
        ->get(route('services', ['search' => 'poster']))
        ->assertOk()
        ->assertSee('Custom poster printing')
        ->assertDontSee('Hidden lamination');

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('Send artwork for a custom poster.')
        ->assertSee('Price provided by quote')
        ->assertSee('Request a quote');
});

test('guests and customers cannot access admin catalog endpoints while employees can', function () {
    $product = Product::create(['name' => 'Active print', 'price' => '5.00', 'is_active' => true]);
    $this->get(route('admin.products.index'))->assertRedirect(route('login'));

    $customer = User::factory()->create();
    $this->actingAs($customer)->get(route('admin.products.index'))->assertForbidden();

    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();
    $this->actingAs($employee)->get(route('admin.products.index'))->assertOk();
    $this->get(route('admin.products.edit', $product))->assertOk();
});

test('inactive accounts cannot access the customer storefront', function () {
    $customer = User::factory()->create();
    $customer->forceFill(['is_active' => false])->save();

    $this->actingAs($customer)->get(route('products.index'))->assertForbidden();
    $this->get(route('services'))->assertForbidden();
});