<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordInventoryMovementRequest;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(): View
    {
        $query = InventoryItem::query()->with('suppliers')->orderBy('name');

        if (request()->filled('search')) {
            $search = request()->string('search')->trim()->toString();
            $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('sku', 'like', '%'.$search.'%'));
        }

        if (request()->boolean('low_stock')) {
            $query->where('is_active', true)->whereColumn('quantity_on_hand', '<=', 'reorder_level');
        }

        $items = $query->paginate(25)->withQueryString();
        $lowStockCount = InventoryItem::where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        return view('admin.inventory.index', compact('items', 'lowStockCount'));
    }

    public function create(): View
    {
        return view('admin.inventory.create', [
            'item' => new InventoryItem(['is_active' => true]),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'categories' => InventoryItem::CATEGORIES,
        ]);
    }

    public function store(StoreInventoryItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $supplierIds = $data['supplier_ids'] ?? [];
        unset($data['supplier_ids']);
        $data['sku'] = $data['sku'] ?? null;
        $data['is_active'] = $request->boolean('is_active');

        $item = InventoryItem::create($data);
        $item->suppliers()->sync($supplierIds);

        return redirect()->route('admin.inventory.show', $item)->with('status', 'Supply item created. Record a receipt to add stock.');
    }

    public function show(InventoryItem $inventoryItem): View
    {
        $inventoryItem->load('suppliers');
        $movements = $inventoryItem->movements()->with(['supplier', 'user'])->latest('created_at')->paginate(20);

        return view('admin.inventory.show', [
            'item' => $inventoryItem,
            'movements' => $movements,
        ]);
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        return view('admin.inventory.edit', [
            'item' => $inventoryItem->load('suppliers'),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'categories' => InventoryItem::CATEGORIES,
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validated();
        $expectedVersion = (int) $data['lock_version'];
        $supplierIds = $data['supplier_ids'] ?? [];
        unset($data['lock_version'], $data['supplier_ids']);
        $data['sku'] = $data['sku'] ?? null;
        $data['is_active'] = $request->boolean('is_active');

        $updated = InventoryItem::query()
            ->whereKey($inventoryItem->id)
            ->where('lock_version', $expectedVersion)
            ->update([...$data, 'lock_version' => $expectedVersion + 1, 'updated_at' => now()]);

        if ($updated !== 1) {
            throw ValidationException::withMessages([
                'conflict' => 'This supply item was changed by another staff member. Reload it and reapply your changes.',
            ]);
        }

        $inventoryItem->suppliers()->sync($supplierIds);

        return redirect()->route('admin.inventory.show', $inventoryItem)->with('status', 'Supply item updated.');
    }

    public function recordMovement(RecordInventoryMovementRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validated();
        $supplier = isset($data['supplier_id']) ? Supplier::findOrFail($data['supplier_id']) : null;

        if ($data['type'] === InventoryMovement::TYPE_RECEIVED && $supplier !== null && ! $supplier->is_active) {
            throw ValidationException::withMessages(['supplier_id' => 'Choose an active supplier for this receipt.']);
        }

        DB::transaction(function () use ($request, $inventoryItem, $data, $supplier): void {
            $item = InventoryItem::query()->whereKey($inventoryItem->id)->lockForUpdate()->firstOrFail();

            if (! $item->is_active) {
                throw ValidationException::withMessages(['inventory_item' => 'Reactivate this supply item before changing its stock.']);
            }

            $scale = 1000;
            $before = (int) round((float) $item->quantity_on_hand * $scale);
            $quantity = (int) round((float) $data['quantity'] * $scale);

            $after = match ($data['type']) {
                InventoryMovement::TYPE_RECEIVED => $before + $quantity,
                InventoryMovement::TYPE_USED => $before - $quantity,
                InventoryMovement::TYPE_STOCKTAKE => $quantity,
            };

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'There is not enough stock to record this usage.',
                ]);
            }

            $item->quantity_on_hand = number_format($after / $scale, 3, '.', '');
            $item->save();

            if ($supplier !== null) {
                $item->suppliers()->syncWithoutDetaching([$supplier->id]);
            }

            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'supplier_id' => $supplier?->id,
                'user_id' => $request->user()->id,
                'type' => $data['type'],
                'quantity_change' => number_format(($after - $before) / $scale, 3, '.', ''),
                'quantity_before' => number_format($before / $scale, 3, '.', ''),
                'quantity_after' => number_format($after / $scale, 3, '.', ''),
                'notes' => $data['notes'] ?? null,
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.inventory.show', $inventoryItem)->with('status', 'Stock movement recorded.');
    }
}