@extends('layouts.app')

@section('title', 'Products | Graphic Zone')

@section('content')
    <section>
        <p class="gz-eyebrow">Shop the catalog</p>
        <h1 class="mt-1 text-2xl font-bold">Printed products</h1>
        <p class="mt-1 text-sm text-gray-600">Browse the products currently available from our print shop.</p>
    </section>

    <form class="mt-5 flex flex-wrap items-end gap-2 border-y border-gray-200 py-4" method="GET" action="{{ route('products.index') }}">
        <div class="min-w-40 flex-1">
            <label class="block text-xs font-medium text-gray-600" for="search">Search products</label>
            <input class="mt-1 w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm" id="search" name="search" value="{{ request('search') }}" type="search">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600" for="paper_size">Paper size</label>
            <select class="mt-1 rounded border border-gray-300 px-2.5 py-1.5 text-sm" id="paper_size" name="paper_size">
                <option value="">All sizes</option>
                @foreach ($paperSizes as $paperSize)<option value="{{ $paperSize }}" @selected(request('paper_size') === $paperSize)>{{ $paperSize }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600" for="paper_finish">Finish</label>
            <select class="mt-1 rounded border border-gray-300 px-2.5 py-1.5 text-sm" id="paper_finish" name="paper_finish">
                <option value="">All finishes</option>
                @foreach ($paperFinishes as $paperFinish)<option value="{{ $paperFinish }}" @selected(request('paper_finish') === $paperFinish)>{{ $paperFinish }}</option>@endforeach
            </select>
        </div>
        <button class="rounded bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white" type="submit">Filter</button>
        <a class="px-2 py-1.5 text-xs underline" href="{{ route('products.index') }}">Clear</a>
    </form>

    @error('quantity')
        <p class="mt-4 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>
    @enderror

    <section class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6" aria-label="Available products">
        @forelse ($products as $product)
            @php
                $hasTrackedVariant = $product->variants->contains(fn ($variant) => $variant->availableQuantity() !== null);
                $hasAvailableVariant = $product->variants->contains(fn ($variant) => $variant->availableQuantity() === null || $variant->availableQuantity() > 0);
            @endphp
            <article class="flex flex-col overflow-hidden rounded border border-gray-200 bg-white transition hover:border-emerald-700 hover:shadow-sm">
                <a class="block bg-gray-50" href="{{ route('products.show', $product) }}">
                    @if ($product->image_path)
                        <img class="aspect-square w-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">
                    @else
                        <span class="block aspect-square w-full bg-gray-100"></span>
                    @endif
                </a>
                <div class="flex flex-1 flex-col gap-1 p-2">
                    <h2 class="line-clamp-2 text-xs leading-snug text-gray-800"><a class="hover:text-emerald-800 hover:underline" href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h2>
                    @if ($product->category)
                        <p class="truncate text-[10px] uppercase tracking-wide text-gray-400">{{ \Illuminate\Support\Str::headline($product->category) }}</p>
                    @endif
                    @if ($product->paper_size || $product->paper_weight || $product->paper_finish)
                        <p class="truncate text-[10px] text-gray-500">
                            @if ($product->paper_size){{ $product->paper_size }}@endif
                            @if ($product->paper_weight)<span class="mx-0.5">&middot;</span>{{ $product->paper_weight }}gsm @endif
                            @if ($product->paper_finish)<span class="mx-0.5">&middot;</span>{{ $product->paper_finish }}@endif
                        </p>
                    @endif
                    <p class="mt-auto pt-1 text-sm font-bold text-[#008da5]">From {{ number_format((float) $product->variants->min('price'), 2) }}</p>
                    <p class="text-[10px] {{ $hasAvailableVariant ? 'text-emerald-700' : 'font-medium text-red-700' }}">
                        {{ ! $hasTrackedVariant ? 'Inventory not tracked' : ($hasAvailableVariant ? 'Available' : 'Out of stock') }}
                    </p>
                    <a class="mt-1 rounded bg-[#008da5] px-2 py-1 text-center text-[10px] font-semibold text-white hover:bg-[#006f83]" href="{{ route('products.show', $product) }}">Choose options</a>
                </div>
            </article>
        @empty
            <p class="col-span-full rounded border border-dashed border-gray-300 py-8 text-center text-sm text-gray-500">There are no products available right now.</p>
        @endforelse
    </section>
    <div class="mt-5">{{ $products->links() }}</div>
@endsection
