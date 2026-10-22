@extends('layouts.app')

@section('title', $product->name.' | Print Studio')

@section('content')
    <section class="max-w-3xl">
        <a class="text-sm underline" href="{{ route('products.index') }}">Back to products</a>
        <p class="mt-5 text-sm font-semibold uppercase text-emerald-800">Printed product</p>
        <h1 class="mt-2 text-3xl font-semibold">{{ $product->name }}</h1>
        <p class="mt-2 text-sm text-gray-500">{{ \Illuminate\Support\Str::headline($product->category) }}</p>
        @if ($product->image_path)<img class="mt-6 aspect-[4/3] w-full max-w-2xl rounded border border-gray-200 object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">@endif
        @if ($product->description)<p class="mt-4 text-gray-600">{{ $product->description }}</p>@endif

        <dl class="mt-7 grid gap-4 border-y border-gray-200 py-5 sm:grid-cols-3">
            @if ($product->paper_size)<div><dt class="text-sm text-gray-500">Paper size</dt><dd class="mt-1 font-medium">{{ $product->paper_size }}</dd></div>@endif
            @if ($product->paper_weight)<div><dt class="text-sm text-gray-500">Paper weight</dt><dd class="mt-1 font-medium">{{ $product->paper_weight }}</dd></div>@endif
            @if ($product->paper_finish)<div><dt class="text-sm text-gray-500">Finish</dt><dd class="mt-1 font-medium">{{ $product->paper_finish }}</dd></div>@endif
        </dl>

        @error('quantity')<p class="mt-4 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
        @php
            $hasAvailableVariant = $product->variants->contains(fn ($variant) => $variant->availableQuantity() === null || $variant->availableQuantity() > 0);
        @endphp
        <form class="mt-6 flex items-end gap-3" method="POST" action="{{ route('cart.items.store') }}">
            @csrf
            <input name="item_type" type="hidden" value="product">
            <input name="item_id" type="hidden" value="{{ $product->id }}">
            <div><label class="block text-sm font-medium" for="variant_id">Choose option</label><select class="mt-1 w-full min-w-56 rounded border border-gray-300 px-3 py-2" id="variant_id" name="variant_id" required>@foreach($product->variants as $variant)@php($availableQuantity = $variant->availableQuantity())<option value="{{ $variant->id }}" @disabled($availableQuantity !== null && $availableQuantity < 1)>{{ $variant->name }}{{ count($variant->options ?? []) ? ' ('.collect($variant->options)->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key).': '.$value)->join(', ').')' : '' }} · {{ number_format((float) $variant->price, 2) }} · {{ $availableQuantity === null ? 'Stock not tracked' : ($availableQuantity.' available') }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium" for="quantity">Quantity</label><input class="mt-1 w-24 rounded border border-gray-300 px-3 py-2" id="quantity" name="quantity" type="number" min="1" max="99" value="1" required></div>
            <button class="rounded bg-emerald-800 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-900 disabled:cursor-not-allowed disabled:opacity-50" type="submit" @disabled(! $hasAvailableVariant)>Add to cart</button>
        </form>
        @unless($hasAvailableVariant)<p class="mt-3 text-sm text-red-700">All options are currently out of stock.</p>@endunless
    </section>
@endsection