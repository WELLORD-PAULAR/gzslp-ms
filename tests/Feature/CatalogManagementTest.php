<?php

use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin can create edit and delete a product', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $this->actingAs($admin);

    $this->get(route('admin.products.index'))->assertOk();
    $this->get(route('admin.products.create'))->assertOk();

    $this->post(route('admin.products.store'), [
        'name' => 'A4 Matte Print',
        'description' => 'Full-color print on matte paper.',
        'price' => '12.50',
        'paper_size' => 'A4',
        'paper_weight' => '150 gsm',
        'paper_finish' => 'Matte',
        'is_active' => '1',
    ])->assertRedirect(route('admin.products.index'));

    $product = Product::firstOrFail();
    $this->get(route('admin.products.edit', $product))->assertOk();

    $this->put(route('admin.products.update', $product), [
        'name' => 'A4 Gloss Print',
        'description' => 'Full-color print on glossy paper.',
        'price' => '15.00',
        'paper_size' => 'A4',
        'paper_weight' => '200 gsm',
        'paper_finish' => 'Gloss',
        'is_active' => '0',
    ])->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'A4 Gloss Print',
        'price' => '15.00',
        'paper_finish' => 'Gloss',
        'is_active' => false,
    ]);

    $this->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('an admin can create edit and delete a service', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $this->actingAs($admin);

    $this->get(route('admin.services.index'))->assertOk();
    $this->get(route('admin.services.create'))->assertOk();

    $this->post(route('admin.services.store'), [
        'name' => 'Custom Artwork Printing',
        'description' => 'Printing from customer-provided artwork.',
        'starting_price' => '20.00',
        'is_active' => '1',
    ])->assertRedirect(route('admin.services.index'));

    $service = Service::firstOrFail();
    $this->get(route('admin.services.edit', $service))->assertOk();

    $this->put(route('admin.services.update', $service), [
        'name' => 'Custom Design and Printing',
        'description' => 'Design assistance and printing.',
        'starting_price' => '25.00',
        'is_active' => '1',
    ])->assertRedirect(route('admin.services.index'));

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Custom Design and Printing',
        'starting_price' => '25.00',
    ]);

    $this->delete(route('admin.services.destroy', $service))
        ->assertRedirect(route('admin.services.index'));

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});

test('only active products and services are listed to customers', function () {
    Product::create([
        'name' => 'Available print',
        'price' => 8,
        'paper_size' => 'A4',
        'paper_weight' => '150 gsm',
        'paper_finish' => 'Matte',
        'is_active' => true,
    ]);
    Product::create(['name' => 'Hidden print', 'price' => 9, 'is_active' => false]);
    Service::create(['name' => 'Available service', 'is_active' => true]);
    Service::create(['name' => 'Hidden service', 'is_active' => false]);

    $this->actingAs(User::factory()->create());

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Available print')
        ->assertSee('A4')
        ->assertSee('150 gsm')
        ->assertSee('Matte')
        ->assertDontSee('Hidden print');

    $this->get(route('services'))
        ->assertOk()
        ->assertSee('Available service')
        ->assertDontSee('Hidden service');
});

test('guests and customers cannot manage catalog records', function () {
    $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    $this->get(route('admin.services.index'))->assertRedirect(route('login'));

    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('admin.products.index'))
        ->assertForbidden();

    $this->get(route('admin.services.index'))->assertForbidden();

    $this->post(route('admin.products.store'), [
        'name' => 'Unauthorized',
        'price' => '10.00',
    ])->assertForbidden();
});

test('catalog forms reject invalid prices and missing names', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->actingAs($admin)
        ->from(route('admin.products.create'))
        ->post(route('admin.products.store'), [
            'name' => 'Invalid price',
            'price' => '-1',
        ])
        ->assertSessionHasErrors('price');

    $this->from(route('admin.services.create'))
        ->post(route('admin.services.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors('name');
});