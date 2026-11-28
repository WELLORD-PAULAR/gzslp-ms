@extends('layouts.app')

@section('title', $product->name.' | Graphic Zone')

@section('content')
    @php
        $hasAvailableVariant = $product->variants->contains(
            fn ($variant) => $variant->availableQuantity() === null || $variant->availableQuantity() > 0
        );
    @endphp

    <section class="max-w-6xl">
        <a class="text-sm underline" href="{{ route('products.index') }}">Back to products</a>

        <p class="mt-5 text-sm font-semibold uppercase text-[#008da5]">Printed product</p>
        <h1 class="mt-2 text-3xl font-semibold">{{ $product->name }}</h1>
        <p class="mt-2 text-sm text-gray-500">{{ \Illuminate\Support\Str::headline($product->category) }}</p>

        @if ($product->description)
            <p class="mt-4 text-gray-600">{{ $product->description }}</p>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,672px)_minmax(0,1fr)]">
            <div>
                @if ($product->image_path)
                    <img
                        class="aspect-[4/3] w-full max-w-2xl rounded border border-gray-200 object-cover"
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}"
                        alt="{{ $product->name }}"
                    >
                @endif

                @if ($product->paper_size || $product->paper_weight || $product->paper_finish)
                    <dl class="mt-5 grid gap-4 border-y border-gray-200 py-5 text-sm sm:grid-cols-3">
                        @if ($product->paper_size)
                            <div>
                                <dt class="text-gray-500">Paper size</dt>
                                <dd class="mt-1 font-medium">{{ $product->paper_size }}</dd>
                            </div>
                        @endif

                        @if ($product->paper_weight)
                            <div>
                                <dt class="text-gray-500">Paper weight</dt>
                                <dd class="mt-1 font-medium">{{ $product->paper_weight }}</dd>
                            </div>
                        @endif

                        @if ($product->paper_finish)
                            <div>
                                <dt class="text-gray-500">Paper finish</dt>
                                <dd class="mt-1 font-medium">{{ $product->paper_finish }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif
            </div>

            <div class="gz-panel h-fit p-5">
                <p class="text-sm font-semibold uppercase text-[#008da5]">
                    Order this print
                </p>

                @error('quantity')
                    <p class="mt-3 text-sm text-red-800" role="alert">{{ $message }}</p>
                @enderror

                <form class="mt-4 grid grid-cols-1 gap-1.5" method="POST" action="{{ route('cart.items.store') }}">
                    @csrf

                    <input name="item_type" type="hidden" value="product">
                    <input name="item_id" type="hidden" value="{{ $product->id }}">

                    <div>
                        <label class="block text-sm font-medium" for="variant_id">
                            Choose option
                        </label>

                        <select
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                            id="variant_id"
                            name="variant_id"
                            required
                        >
                            @foreach ($product->variants as $variant)
                                @php($availableQuantity = $variant->availableQuantity())

                                <option
                                    value="{{ $variant->id }}"
                                    @disabled($availableQuantity !== null && $availableQuantity < 1)
                                >
                                    {{ $variant->name }}
                                    {{ count($variant->options ?? []) ? ' ('.collect($variant->options)->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key).': '.$value)->join(', ').')' : '' }}
                                    · {{ number_format((float) $variant->price, 2) }}
                                    · {{ $availableQuantity === null ? 'Stock not tracked' : ($availableQuantity.' available') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label class="mt-3 text-[10px] text-gray-500" for="quantity">
                        Qty
                    </label>

                    <div class="gz-qty-stepper gz-qty-stepper-touch gz-qty-stepper-card" data-qty-stepper>
                        <button
                            class="gz-qty-step"
                            type="button"
                            data-qty-step="-1"
                            aria-label="Decrease quantity"
                            aria-controls="quantity"
                        >−</button>

                        <input
                            class="gz-qty-input"
                            id="quantity"
                            name="quantity"
                            type="number"
                            min="1"
                            max="99"
                            value="{{ old('quantity', 1) }}"
                            required
                        >

                        <button
                            class="gz-qty-step"
                            type="button"
                            data-qty-step="1"
                            aria-label="Increase quantity"
                            aria-controls="quantity"
                        >+</button>
                    </div>

                    <button
                        class="gz-btn gz-btn-cyan mt-3 w-full disabled:cursor-not-allowed disabled:opacity-50"
                        type="submit"
                        @disabled(! $hasAvailableVariant)
                    >
                        Add to cart
                    </button>
                </form>

                @unless ($hasAvailableVariant)
                    <p class="mt-3 text-sm text-red-700">
                        All options are currently out of stock.
                    </p>
                @endunless
            </div>
        </div>
    </section>
@endsection
