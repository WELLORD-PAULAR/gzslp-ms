<?php

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeShopEmployee(): User
{
    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();

    return $employee;
}

test('staff can receive supplies, record usage, and reconcile stock with an audit trail', function () {
    $employee = makeShopEmployee();
    $supplier = Supplier::create(['name' => 'Paper Wholesale', 'is_active' => true]);
    $item = InventoryItem::create([
        'name' => 'A4 matte paper',
        'sku' => 'PAPER-A4-MATTE',
        'category' => 'paper',
        'unit' => 'sheet',
        'quantity_on_hand' => '0.000',
        'reorder_level' => '20.000',
        'is_active' => true,
    ]);

    $this->actingAs($employee)
        ->post(route('admin.inventory.movements.store', $item), [
            'type' => 'received',
            'quantity' => '100',
            'supplier_id' => $supplier->id,
            'notes' => 'Delivery receipt 001',
        ])
        ->assertRedirect(route('admin.inventory.show', $item));

    expect($item->fresh()->quantity_on_hand)->toBe('100.000')
        ->and($item->fresh()->suppliers->modelKeys())->toContain($supplier->id);

    $this->post(route('admin.inventory.movements.store', $item), [
        'type' => 'used',
        'quantity' => '15.5',
        'notes' => 'Manual usage for test prints',
    ])->assertRedirect(route('admin.inventory.show', $item));

    expect($item->fresh()->quantity_on_hand)->toBe('84.500');

    $this->post(route('admin.inventory.movements.store', $item), [
        'type' => 'stocktake',
        'quantity' => '82',
        'notes' => 'Physical count',
    ])->assertRedirect(route('admin.inventory.show', $item));

    expect($item->fresh()->quantity_on_hand)->toBe('82.000')
        ->and($item->movements()->count())->toBe(3)
        ->and($item->movements()->latest('created_at')->first()->quantity_change)->toBe('-2.500');

    $this->get(route('admin.inventory.show', $item))
        ->assertOk()
        ->assertSee('Movement history')
        ->assertSee('Physical count');
});

test('staff cannot record usage below zero and failed movements do not change stock', function () {
    $employee = makeShopEmployee();
    $item = InventoryItem::create([
        'name' => 'Blank mug',
        'category' => 'blank_mug',
        'unit' => 'piece',
        'quantity_on_hand' => '2.000',
        'reorder_level' => '3.000',
        'is_active' => true,
    ]);

    $this->actingAs($employee)
        ->from(route('admin.inventory.show', $item))
        ->post(route('admin.inventory.movements.store', $item), [
            'type' => 'used',
            'quantity' => '2.5',
        ])
        ->assertSessionHasErrors('quantity');

    expect($item->fresh()->quantity_on_hand)->toBe('2.000')
        ->and($item->movements()->count())->toBe(0);
});

test('low-stock filtering includes supplies at or below their threshold', function () {
    $employee = makeShopEmployee();
    $low = InventoryItem::create([
        'name' => 'Tarpaulin roll',
        'category' => 'tarpaulin_material',
        'unit' => 'meter',
        'quantity_on_hand' => '4.000',
        'reorder_level' => '5.000',
        'is_active' => true,
    ]);
    InventoryItem::create([
        'name' => 'Ink bottle',
        'category' => 'ink',
        'unit' => 'bottle',
        'quantity_on_hand' => '10.000',
        'reorder_level' => '2.000',
        'is_active' => true,
    ]);
    InventoryItem::create([
        'name' => 'Inactive stock',
        'category' => 'other',
        'unit' => 'piece',
        'quantity_on_hand' => '0.000',
        'reorder_level' => '5.000',
        'is_active' => false,
    ]);

    $this->actingAs($employee)
        ->get(route('admin.inventory.index', ['low_stock' => 1]))
        ->assertOk()
        ->assertSee('Tarpaulin roll')
        ->assertDontSee('Ink bottle')
        ->assertDontSee('Inactive stock');
});

test('supply item metadata updates use optimistic locking', function () {
    $employee = makeShopEmployee();
    $item = InventoryItem::create([
        'name' => 'Cotton shirt blank',
        'category' => 'blank_shirt',
        'unit' => 'piece',
        'quantity_on_hand' => '10.000',
        'reorder_level' => '3.000',
        'is_active' => true,
    ]);

    $this->actingAs($employee)
        ->put(route('admin.inventory.update', $item), [
            'name' => 'Cotton shirt blank',
            'category' => 'blank_shirt',
            'unit' => 'piece',
            'reorder_level' => '4.000',
            'is_active' => '1',
            'lock_version' => 0,
        ])
        ->assertRedirect(route('admin.inventory.show', $item));

    $this->from(route('admin.inventory.edit', $item))
        ->put(route('admin.inventory.update', $item), [
            'name' => 'Stale update',
            'category' => 'blank_shirt',
            'unit' => 'piece',
            'reorder_level' => '6.000',
            'is_active' => '1',
            'lock_version' => 0,
        ])
        ->assertSessionHasErrors('conflict');

    expect($item->fresh()->name)->toBe('Cotton shirt blank')
        ->and($item->fresh()->reorder_level)->toBe('4.000')
        ->and($item->fresh()->lock_version)->toBe(1);
});

test('staff can create and deactivate suppliers without deleting supplier history', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->actingAs($admin)
        ->post(route('admin.suppliers.store'), [
            'name' => 'Blank Apparel Co.',
            'email' => 'supplies@example.com',
            'phone' => '5550100',
            'address' => 'Warehouse 1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.suppliers.index'));

    $supplier = Supplier::firstOrFail();
    $item = InventoryItem::create([
        'name' => 'Shirt blank',
        'category' => 'blank_shirt',
        'unit' => 'piece',
        'quantity_on_hand' => '0.000',
        'reorder_level' => '0.000',
        'is_active' => true,
    ]);
    $item->suppliers()->attach($supplier);

    $this->put(route('admin.suppliers.update', $supplier), [
        'name' => 'Blank Apparel Co.',
        'email' => 'supplies@example.com',
        'phone' => '5550100',
        'address' => 'Warehouse 1',
        'is_active' => '0',
    ])->assertRedirect(route('admin.suppliers.index'));

    expect($supplier->fresh()->is_active)->toBeFalse()
        ->and($item->fresh()->suppliers->modelKeys())->toContain($supplier->id);
});

test('staff can delete a supplier without deleting inventory movement history', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $supplier = Supplier::create(['name' => 'Paper Wholesaler']);
    $item = InventoryItem::create([
        'name' => 'A4 paper',
        'category' => 'paper',
        'unit' => 'sheet',
        'quantity_on_hand' => '20.000',
        'reorder_level' => '5.000',
        'is_active' => true,
    ]);
    $item->suppliers()->attach($supplier);
    $movement = InventoryMovement::create([
        'inventory_item_id' => $item->id,
        'supplier_id' => $supplier->id,
        'user_id' => $admin->id,
        'type' => InventoryMovement::TYPE_RECEIVED,
        'quantity_change' => '20.000',
        'quantity_before' => '0.000',
        'quantity_after' => '20.000',
        'notes' => 'Opening stock',
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.suppliers.destroy', $supplier))
        ->assertRedirect(route('admin.suppliers.index'));

    $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    $this->assertDatabaseHas('inventory_movements', ['id' => $movement->id, 'supplier_id' => null]);
    expect($item->fresh()->suppliers)->toBeEmpty();
});

test('inventory and supplier management is staff-only', function () {
    $this->get(route('admin.inventory.index'))->assertRedirect(route('login'));
    $this->get(route('admin.suppliers.index'))->assertRedirect(route('login'));

    $customer = User::factory()->create();
    $this->actingAs($customer)->get(route('admin.inventory.index'))->assertForbidden();
    $this->get(route('admin.suppliers.index'))->assertForbidden();

    $employee = makeShopEmployee();
    $this->actingAs($employee)->get(route('admin.inventory.index'))->assertOk();
    $this->get(route('admin.suppliers.index'))->assertOk();
});