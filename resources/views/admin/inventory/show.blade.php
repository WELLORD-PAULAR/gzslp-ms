@extends('layouts.admin')

@section('title', $item->name.' | Inventory')

@section('content')
    <section>
        <a class="text-sm underline" href="{{ route('admin.inventory.index') }}">Back to inventory</a>
        <div class="mt-4 flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm font-semibold uppercase text-gray-500">{{ \Illuminate\Support\Str::headline($item->category) }}</p><h1 class="mt-2 text-3xl font-semibold">{{ $item->name }}</h1>@if($item->sku)<p class="mt-2 text-sm text-gray-500">SKU {{ $item->sku }}</p>@endif</div><a class="rounded border border-gray-300 px-4 py-2 text-sm font-medium" href="{{ route('admin.inventory.edit', $item) }}">Edit supply details</a></div>
    </section>

    @if(session('status'))<p class="mt-5 border-l-4 border-cyan-500 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<div class="mt-5 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <section class="mt-7 grid gap-8 lg:grid-cols-[1fr_22rem]">
        <div>
            <div class="grid gap-5 border-y border-gray-200 py-5 sm:grid-cols-3"><div><p class="text-sm text-gray-500">On hand</p><p class="mt-1 text-2xl font-semibold {{ $item->isLowStock() ? 'text-amber-900' : '' }}">{{ number_format((float) $item->quantity_on_hand, 3) }} {{ $item->unit }}</p></div><div><p class="text-sm text-gray-500">Reorder threshold</p><p class="mt-1 text-lg font-medium">{{ number_format((float) $item->reorder_level, 3) }} {{ $item->unit }}</p></div><div><p class="text-sm text-gray-500">Suppliers</p><p class="mt-1 text-sm">{{ $item->suppliers->pluck('name')->join(', ') ?: 'None assigned' }}</p></div></div>
            <h2 class="mt-8 text-xl font-semibold">Movement history</h2>
            <div class="mt-4 overflow-x-auto border-y border-gray-200 bg-white"><table class="w-full min-w-[42rem] text-left text-sm"><thead class="border-b border-gray-200 text-gray-500"><tr><th class="px-3 py-3 font-medium">Date</th><th class="px-3 py-3 font-medium">Action</th><th class="px-3 py-3 font-medium">Change</th><th class="px-3 py-3 font-medium">Balance</th><th class="px-3 py-3 font-medium">Recorded by</th><th class="px-3 py-3 font-medium">Notes</th></tr></thead><tbody class="divide-y divide-gray-100">
                @forelse($movements as $movement)
                    <tr><td class="px-3 py-3">{{ $movement->created_at->format('M j, Y g:i A') }}</td><td class="px-3 py-3">{{ \Illuminate\Support\Str::headline($movement->type) }}@if($movement->supplier)<span class="mt-1 block text-gray-500">{{ $movement->supplier->name }}</span>@endif</td><td class="px-3 py-3">{{ (float) $movement->quantity_change > 0 ? '+' : '' }}{{ number_format((float) $movement->quantity_change, 3) }} {{ $item->unit }}</td><td class="px-3 py-3">{{ number_format((float) $movement->quantity_before, 3) }} → {{ number_format((float) $movement->quantity_after, 3) }}</td><td class="px-3 py-3">{{ $movement->user->name }}</td><td class="px-3 py-3">{{ $movement->notes ?: '—' }}</td></tr>
                @empty
                    <tr><td class="px-3 py-7 text-center text-gray-500" colspan="6">No stock movements yet.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="mt-4">
                {{ $movements->links() }}
                @include('partials.pagination-status', ['paginator' => $movements])
            </div>
        </div>

        <aside class="h-fit border-y border-gray-200 py-5">
            <h2 class="font-semibold">Record stock movement</h2>
            <p class="mt-2 text-sm text-gray-600">Receiving increases stock; usage decreases it; stocktake sets the balance to a counted amount.</p>
            <form class="mt-5 space-y-4" method="POST" action="{{ route('admin.inventory.movements.store', $item) }}" onsubmit="return window.confirm('Record this stock movement? The on-hand balance will change.')">
                @csrf
                <div><label class="block text-sm font-medium" for="type">Movement</label><select class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="type" name="type" required><option value="received" @selected(old('type') === 'received')>Receive supplies</option><option value="used" @selected(old('type') === 'used')>Record usage</option><option value="stocktake" @selected(old('type') === 'stocktake')>Set counted balance</option></select>@error('type')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium" for="quantity">Quantity / counted balance</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="quantity" name="quantity" type="number" min="0" max="999999999.999" step="0.001" value="{{ old('quantity') }}" required>@error('quantity')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium" for="supplier_id">Supplier <span class="font-normal text-gray-500">(required for receipt)</span></label><select class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="supplier_id" name="supplier_id"><option value="">Select supplier</option>@foreach($item->suppliers->where('is_active', true) as $supplier)<option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>@endforeach</select>@error('supplier_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium" for="notes">Reference / reason</label><textarea class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="notes" name="notes" rows="3" maxlength="2000">{{ old('notes') }}</textarea>@error('notes')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                @error('inventory_item')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                <button class="w-full rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit" @disabled(! $item->is_active)>Record movement</button>
            </form>
        </aside>
    </section>
@endsection