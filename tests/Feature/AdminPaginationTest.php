<?php

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function paginationAdmin(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    return $admin;
}

test('admin account listing paginates and preserves filters in pagination links', function () {
    $admin = paginationAdmin();

    foreach (range(1, 26) as $number) {
        User::factory()->create([
            'name' => 'Pagination customer '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'email' => 'pagination-'.$number.'@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertViewHas('users', fn ($users) => $users->total() === 27 && $users->count() === 25)
        ->assertSee('Page 1 of 2');

    $this->get(route('admin.users.index', ['page' => 2]))
        ->assertOk()
        ->assertViewHas('users', fn ($users) => $users->count() === 2)
        ->assertSee('Page 2 of 2');

    $this->get(route('admin.users.index', [
        'search' => 'Pagination customer',
        'role' => User::ROLE_CUSTOMER,
    ]))
        ->assertOk()
        ->assertViewHas('users', fn ($users) => $users->total() === 26 && $users->count() === 25)
        ->assertSee('page=2', false)
        ->assertSee('role=customer', false)
        ->assertSee('search=Pagination%20customer', false)
        ->assertSee('Page 1 of 2');

    $this->get(route('admin.users.index', [
        'search' => 'Pagination customer',
        'role' => User::ROLE_CUSTOMER,
        'page' => 2,
    ]))
        ->assertOk()
        ->assertViewHas('users', fn ($users) => $users->count() === 1)
        ->assertSee('Page 2 of 2');
});

test('pagination shows page one of one when the listing fits on a single page', function () {
    $this->actingAs(paginationAdmin())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Page 1 of 1');
});

test('every other admin listing paginates at its configured page size', function () {
    $admin = paginationAdmin();
    $customer = User::factory()->create();

    foreach (range(1, 26) as $number) {
        Product::create(['name' => 'Page product '.$number, 'price' => '10.00', 'is_active' => true]);
        Service::create(['name' => 'Page service '.$number, 'starting_price' => '10.00', 'is_active' => true]);
        Supplier::create(['name' => 'Page supplier '.$number, 'is_active' => true]);
        InventoryItem::create([
            'name' => 'Page supply '.$number,
            'sku' => 'PAGE-'.$number,
            'category' => 'paper',
            'unit' => 'sheet',
            'quantity_on_hand' => '10.000',
            'reorder_level' => '1.000',
            'is_active' => true,
        ]);
        $customer->orders()->create(['status' => Order::STATUS_PENDING, 'subtotal' => '10.00']);
    }

    $item = InventoryItem::firstOrFail();
    foreach (range(1, 26) as $number) {
        InventoryMovement::create([
            'inventory_item_id' => $item->id,
            'user_id' => $admin->id,
            'type' => InventoryMovement::TYPE_RECEIVED,
            'quantity_change' => '1.000',
            'quantity_before' => '0.000',
            'quantity_after' => '1.000',
            'created_at' => now()->subMinutes($number),
        ]);
    }

    $listings = [
        [route('admin.products.index'), 'products', 25],
        [route('admin.services.index'), 'services', 25],
        [route('admin.suppliers.index'), 'suppliers', 25],
        [route('admin.inventory.index'), 'items', 25],
        [route('admin.orders.index'), 'orders', 20],
        [route('admin.reports.index'), 'orders', 20],
        [route('admin.inventory.show', $item), 'movements', 20],
    ];

    $this->actingAs($admin);

    foreach ($listings as [$url, $variable, $pageSize]) {
        $this->get($url)
            ->assertOk()
            ->assertViewHas($variable, fn ($paginator) => $paginator->total() === 26 && $paginator->count() === min(26, $pageSize))
            ->assertSee('Page 1 of 2');

        $this->get($url.'?page=2')
            ->assertOk()
            ->assertViewHas($variable, fn ($paginator) => $paginator->count() === 26 - $pageSize)
            ->assertSee('Page 2 of 2');
    }
});
