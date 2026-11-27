<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

function workspaceAdmin(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    return $admin;
}

test('phone numbers are stored as integers and reject formatted input', function () {
    $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Formatted Phone',
        'email' => 'formatted@example.com',
        'phone' => '+63 917 000 0000',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors([
        'phone' => 'The phone number must contain digits only (no spaces, dashes, or symbols).',
    ]);

    $this->post(route('register.store'), [
        'name' => 'Numeric Phone',
        'email' => 'numeric@example.com',
        'phone' => 639170000000,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('account.dashboard'));

    expect(User::where('email', 'numeric@example.com')->firstOrFail()->phone)->toBe(639170000000);
});

test('supplier phone numbers are stored as integers', function () {
    $this->actingAs(workspaceAdmin())
        ->from(route('admin.suppliers.create'))
        ->post(route('admin.suppliers.store'), [
            'name' => 'Formatted Supplier',
            'phone' => '+63 917 000 0000',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.suppliers.create'))
        ->assertSessionHasErrors('phone');

    $this->post(route('admin.suppliers.store'), [
        'name' => 'Numeric Supplier',
        'phone' => 639170000000,
        'is_active' => '1',
    ])->assertRedirect(route('admin.suppliers.index'));

    expect(Supplier::where('name', 'Numeric Supplier')->firstOrFail()->phone)->toBe(639170000000);
});

test('employees cannot see or change order status', function () {
    $admin = workspaceAdmin();
    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '20.00',
    ]);

    $this->actingAs($employee)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertDontSee('Status');

    $this->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertDontSee('Next status');

    $this->patch(route('admin.orders.update', $order), [
        'status' => Order::STATUS_PROCESSING,
    ])->assertForbidden();

    expect($order->fresh()->status)->toBe(Order::STATUS_PENDING);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Next status');
});

test('quote language only appears for orders that need a quote', function () {
    $customer = User::factory()->create();
    $priced = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '20.00',
    ]);
    $priced->items()->create([
        'item_name' => 'Priced print',
        'unit_price' => '20.00',
        'quantity' => 1,
    ]);
    $quoted = $customer->orders()->create([
        'status' => Order::STATUS_QUOTE_REQUESTED,
        'subtotal' => '0.00',
    ]);
    $quoted->items()->create([
        'item_name' => 'Custom print',
        'quantity' => 1,
    ]);

    $this->actingAs($customer)
        ->get(route('orders.show', $priced))
        ->assertOk()
        ->assertSee('Order total')
        ->assertDontSee('Quote pending')
        ->assertDontSee('preparing a quote');

    $this->get(route('orders.show', $quoted))
        ->assertOk()
        ->assertSee('preparing a quote');

    $this->actingAs(workspaceAdmin())
        ->get(route('admin.orders.show', $priced))
        ->assertOk()
        ->assertSee('Order total')
        ->assertDontSee('Pre-quote subtotal')
        ->assertDontSee('Quoted total');
});

test('image upload failures explain why the file was rejected', function () {
    $this->actingAs(workspaceAdmin())
        ->from(route('admin.products.create'))
        ->post(route('admin.products.store'), [
            'name' => 'Broken image product',
            'category' => 'paper',
            'price' => '10.00',
            'image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ])
        ->assertRedirect(route('admin.products.create'))
        ->assertSessionHasErrors('image');

    $this->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('The image was not uploaded.')
        ->assertSee('not a valid image');
});

test('admin catalog listings are paginated', function () {
    foreach (range(1, 26) as $number) {
        Product::create([
            'name' => 'Catalog product '.$number,
            'price' => '10.00',
            'is_active' => true,
        ]);
        Service::create([
            'name' => 'Catalog service '.$number,
            'starting_price' => '10.00',
            'is_active' => true,
        ]);
    }

    $this->actingAs(workspaceAdmin())
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertViewHas('products', fn ($products) => $products->total() === 26 && $products->count() === 25);

    $this->get(route('admin.products.index', ['page' => 2]))
        ->assertOk()
        ->assertViewHas('products', fn ($products) => $products->count() === 1);

    $this->get(route('admin.services.index'))
        ->assertOk()
        ->assertViewHas('services', fn ($services) => $services->total() === 26 && $services->count() === 25);
});
