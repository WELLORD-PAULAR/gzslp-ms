@extends('layouts.admin')

@section('title', 'Product options | Admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a class="text-sm underline" href="{{ route('admin.products.index') }}">Back to products</a>
            <p class="mt-4 text-sm font-semibold uppercase text-gray-500">{{ \Illuminate\Support\Str::headline($product->category) }}</p>
            <h1 class="mt-1 text-3xl font-semibold">{{ $product->name }} options</h1>
        </div>
        <a class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('admin.products.variants.create', $product) }}">Add option</a>
    </div>

    @if(session('status'))<p class="mt-5 border-l-4 border-emerald-700 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif

    <div class="mt-7 overflow-x-auto border-y border-gray-200 bg-white"><table class="w-full min-w-[44rem] text-left text-sm"><thead class="border-b border-gray-200 text-gray-500"><tr><th class="px-4 py-3 font-medium">Option</th><th class="px-4 py-3 font-medium">Price</th><th class="px-4 py-3 font-medium">Supply recipe</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Action</th></tr></thead><tbody class="divide-y divide-gray-100">
        @forelse($variants as $variant)
            <tr><td class="px-4 py-3">{{ $variant->name }}@if($variant->is_default)<span class="ml-2 text-xs text-gray-500">Default</span>@endif @if($variant->sku)<span class="mt-1 block text-gray-500">{{ $variant->sku }}</span>@endif @if($variant->options)<span class="mt-1 block text-gray-500">{{ collect($variant->options)->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key).': '.$value)->join(' · ') }}</span>@endif</td><td class="px-4 py-3">{{ number_format((float) $variant->price, 2) }}</td><td class="px-4 py-3">{{ $variant->supplies_count }} supply type(s)</td><td class="px-4 py-3">{{ $variant->is_active ? 'Available' : 'Hidden' }}</td><td class="px-4 py-3 text-right"><a class="underline" href="{{ route('admin.products.variants.edit', [$product, $variant]) }}">Edit</a></td></tr>
        @empty
            <tr><td class="px-4 py-8 text-center text-gray-500" colspan="5">No variants defined.</td></tr>
        @endforelse
    </tbody></table></div>
    <p class="mt-4 text-sm text-gray-600">Each recipe quantity is consumed from raw supplies per one finished unit when an order enters production. Default options preserve compatibility with existing products.</p>
@endsection