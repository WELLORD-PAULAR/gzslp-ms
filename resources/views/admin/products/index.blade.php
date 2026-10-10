@extends('layouts.admin')

@section('title', 'Products | Admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">CATALOG</p>
            <h1 class="mt-1 text-3xl font-semibold">Products</h1>
        </div>
        <a class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('admin.products.create') }}">Add product</a>
    </div>

    @if (session('status'))
        <p class="mt-5 border-l-4 border-emerald-700 bg-white px-4 py-3 text-sm">{{ session('status') }}</p>
    @endif

    <div class="mt-6 overflow-x-auto border-y border-gray-200 bg-white">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-500">
                <tr><th class="px-4 py-3 font-medium">Product</th><th class="px-4 py-3 font-medium">Price</th><th class="px-4 py-3 font-medium">Visibility</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-3"><span class="font-medium">{{ $product->name }}</span>@if($product->description)<span class="mt-1 block max-w-lg truncate text-gray-500">{{ $product->description }}</span>@endif</td>
                        <td class="px-4 py-3">{{ number_format((float) $product->price, 2) }}</td>
                        <td class="px-4 py-3">{{ $product->is_active ? 'Visible' : 'Hidden' }}</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-4"><a class="underline" href="{{ route('admin.products.edit', $product) }}">Edit</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return window.confirm('Delete this product?')">@csrf @method('DELETE')<button class="text-red-700 underline" type="submit">Delete</button></form></div></td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-gray-500" colspan="4">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection