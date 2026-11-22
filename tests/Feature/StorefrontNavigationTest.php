<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the storefront navigation links to home, services, and cart', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('home'), false)
        ->assertDontSee('>Services</a>', false)
        ->assertDontSee('>Products</a>', false)
        ->assertDontSee('>Cart ', false)
        ->assertSee(route('login'), false)
        ->assertSee('Start your order')
        ->assertSee(route('register'), false);
});

test('login and registration open over the storefront', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('role="dialog"', false)
        ->assertSee('name="email"', false);

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('role="dialog"', false)
        ->assertSee('name="password_confirmation"', false);
});

test('guests are sent to the login overlay before services or cart', function () {
    $this->get(route('services'))->assertRedirect(route('login'));
    $this->get(route('cart'))->assertRedirect(route('login'));
});

test('a signed-in customer can see and visit services and cart', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('products.index'), false)
        ->assertSee(route('services'), false)
        ->assertSee(route('cart'), false)
        ->assertSee('Explore products');

    Service::create(['name' => 'Custom artwork print']);
    Product::create(['name' => 'A4 matte print', 'price' => 12.50]);

    $this->get(route('products.index'))->assertOk()->assertSee('A4 matte print');
    $this->get(route('services'))->assertOk()->assertSee('Custom artwork print');
    $this->get(route('cart'))->assertOk()->assertSee('Your cart is empty.');
});

test('a staff member can preview the customer storefront and exit back to the workspace', function () {
    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();

    $this->actingAs($employee)
        ->get(route('home'))
        ->assertRedirect(route('admin.dashboard'));

    $this->get(route('admin.storefront.preview'))
        ->assertRedirect(route('home'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('products.index'), false)
        ->assertSee(route('services'), false)
        ->assertSee(route('cart'), false)
        ->assertSee('Back to workspace')
        ->assertDontSee('>Workspace</a>', false);

    $this->get(route('cart'))->assertOk();
    $this->get(route('services'))->assertOk();

    $this->get(route('admin.storefront.exit'))
        ->assertRedirect(route('admin.dashboard'));

    $this->get(route('home'))->assertRedirect(route('admin.dashboard'));
    $this->get(route('cart'))->assertForbidden();
});

test('customers cannot enter the staff storefront preview', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.storefront.preview'))
        ->assertForbidden();
});