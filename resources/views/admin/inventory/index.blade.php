@extends('layouts.admin')

@section('title', 'Supply inventory | Admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase text-gray-500">Raw supplies</p>
            <h1 class="mt-2 text-3xl font-semibold">Inventory</h1>
            <p class="mt-2 text-sm text-gray-600">Track production supplies, not finished catalog products.</p>
        </div>
        <a class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('admin.inventory.create') }}">Add supply item</a>
    </div>

    @if (session('status'))<p class="mt-5 border-l-4 border-cyan-500 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif

    <a class="mt-6 inline-flex rounded border border-amber-400 px-3 py-2 text-sm font-medium text-amber-900" href="{{ route('admin.inventory.index', ['low_stock' => 1]) }}">Low stock ({{ $lowStockCount }})</a>

    <form class="mt-5 flex flex-wrap items-end gap-3 print:hidden" method="GET" action="{{ route('admin.inventory.index') }}">
        <div class="min-w-48 flex-1"><label class="block text-sm font-medium" for="search">Supply name or SKU</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="search" name="search" value="{{ request('search') }}"></div>
        @if(request('low_stock'))<input name="low_stock" type="hidden" value="1">@endif
        <button class="rounded border border-gray-300 px-4 py-2 text-sm font-medium" type="submit">Search</button>
        <a class="px-2 py-2 text-sm underline" href="{{ route('admin.inventory.index') }}">Clear</a>
    </form>

    <div class="mt-6 overflow-x-auto border-y border-gray-200 bg-white">
        <table class="w-full min-w-[44rem] text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-500"><tr><th class="px-4 py-3 font-medium">Supply</th><th class="px-4 py-3 font-medium">Category</th><th class="px-4 py-3 font-medium">On hand</th><th class="px-4 py-3 font-medium">Reorder at</th><th class="px-4 py-3 font-medium">Suppliers</th><th class="px-4 py-3 text-right font-medium">Status</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-3"><a class="font-medium underline" href="{{ route('admin.inventory.show', $item) }}">{{ $item->name }}</a>@if($item->sku)<span class="mt-1 block text-gray-500">{{ $item->sku }}</span>@endif</td>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::headline($item->category) }}</td>
                        <td class="px-4 py-3 {{ $item->isLowStock() ? 'font-semibold text-amber-900' : '' }}">{{ number_format((float) $item->quantity_on_hand, 3) }} {{ $item->unit }}@if($item->isLowStock())<span class="ml-2 text-xs">Low</span>@endif</td>
                        <td class="px-4 py-3">{{ number_format((float) $item->reorder_level, 3) }} {{ $item->unit }}</td>
                        <td class="px-4 py-3">{{ $item->suppliers->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $item->is_active ? 'Active' : 'Inactive' }} · <a class="underline" href="{{ route('admin.inventory.edit', $item) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-gray-500" colspan="6">No supply items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">
        {{ $items->links() }}
        @include('partials.pagination-status', ['paginator' => $items])
    </div>
@endsection