<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin can filter an order activity report and see honest order metrics', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $customer = User::factory()->create();

    $completed = $customer->orders()->create([
        'status' => Order::STATUS_COMPLETED,
        'subtotal' => '40.00',
        'quoted_total' => '55.00',
    ]);
    $completed->forceFill(['created_at' => '2026-10-05 12:00:00'])->save();

    $customer->orders()->create([
        'status' => Order::STATUS_QUOTE_REQUESTED,
        'subtotal' => '12.00',
        'created_at' => '2026-10-06 12:00:00',
    ]);
    $outsideRange = $customer->orders()->create([
        'status' => Order::STATUS_COMPLETED,
        'subtotal' => '100.00',
    ]);
    $outsideRange->forceFill(['created_at' => '2026-09-30 12:00:00'])->save();

    Product::create(['name' => 'Live product', 'price' => '5.00', 'is_active' => true]);
    Product::create(['name' => 'Hidden product', 'price' => '5.00', 'is_active' => false]);
    Service::create(['name' => 'Live service', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.reports.index', ['date_from' => '2026-10-01', 'date_to' => '2026-10-31']))
        ->assertOk()
        ->assertSee('Orders submitted')
        ->assertSee('Completed order value')
        ->assertSee('Based on completed orders')
        ->assertSee('55.00')
        ->assertSee('Quote requested')
        ->assertSee('Orders requiring a quote')
        ->assertSee('Current quantities are tracked on the Inventory screen.')
        ->assertSee('Active products')
        ->assertDontSee('100.00');
});

test('only admins can access reports', function () {
    $this->get(route('admin.reports.index'))->assertRedirect(route('login'));

    $customer = User::factory()->create();
    $this->actingAs($customer)->get(route('admin.reports.index'))->assertForbidden();
});

test('report date filters reject an inverted range', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->actingAs($admin)
        ->from(route('admin.reports.index'))
        ->get(route('admin.reports.index', ['date_from' => '2026-10-20', 'date_to' => '2026-10-01']))
        ->assertSessionHasErrors(['date_from', 'date_to']);
});