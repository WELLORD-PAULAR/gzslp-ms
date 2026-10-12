@extends('layouts.admin')

@section('title', 'Suppliers | Admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold uppercase text-gray-500">Supply chain</p><h1 class="mt-2 text-3xl font-semibold">Suppliers</h1></div><a class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('admin.suppliers.create') }}">Add supplier</a></div>
    @if(session('status'))<p class="mt-5 border-l-4 border-emerald-700 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif
    <div class="mt-7 overflow-x-auto border-y border-gray-200 bg-white"><table class="w-full min-w-[40rem] text-left text-sm"><thead class="border-b border-gray-200 text-gray-500"><tr><th class="px-4 py-3 font-medium">Supplier</th><th class="px-4 py-3 font-medium">Contact</th><th class="px-4 py-3 font-medium">Supply items</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Action</th></tr></thead><tbody class="divide-y divide-gray-100">
        @forelse($suppliers as $supplier)
            <tr><td class="px-4 py-3 font-medium">{{ $supplier->name }}</td><td class="px-4 py-3">{{ $supplier->email ?: '—' }}@if($supplier->phone)<span class="mt-1 block text-gray-500">{{ $supplier->phone }}</span>@endif</td><td class="px-4 py-3">{{ $supplier->inventory_items_count }}</td><td class="px-4 py-3">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</td><td class="px-4 py-3 text-right"><a class="underline" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a></td></tr>
        @empty
            <tr><td class="px-4 py-8 text-center text-gray-500" colspan="5">No suppliers recorded.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-5">{{ $suppliers->links() }}</div>
@endsection