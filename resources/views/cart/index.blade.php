@extends('layouts.app')

@section('title', 'Cart | Graphic Zone')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-[#008da5]">Your order</p>
        <h1 class="mt-2 text-3xl font-semibold">Shopping cart</h1>
    </section>

    @if (session('status'))
        <p class="mt-5 border-l-4 border-cyan-500 bg-white px-4 py-3 text-sm" role="status">
            {{ session('status') }}
        </p>
    @endif

    @if ($errors->has('cart') || $errors->has('quantity'))
        <div class="mt-5 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($items->isEmpty())
        <section class="mt-8 border-y border-gray-200 py-8">
            <p class="text-gray-600">Your cart is empty.</p>

            <div class="mt-5 flex flex-wrap gap-3">
                <a class="gz-btn gz-btn-cyan" href="{{ route('products.index') }}">
                    Browse products
                </a>

                <a class="inline-flex rounded border border-gray-300 px-4 py-2 font-medium hover:bg-white" href="{{ route('services') }}">
                    Browse services
                </a>
            </div>
        </section>
    @else
        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_18rem]">
            <section class="divide-y divide-gray-200 border-y border-gray-200" aria-label="Cart items">

                @foreach ($items as $item)
                    <article class="py-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-lg font-semibold">{{ $item->name }}</h2>

                                @if ($item->productVariant?->options)
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ collect($item->productVariant->options)->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key).': '.$value)->join(' · ') }}
                                    </p>
                                @endif

                                @if ($item->product?->description || $item->service?->description)
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $item->product?->description ?? $item->service?->description }}
                                    </p>
                                @endif

                                @if ($item->product && ($item->product->paper_size || $item->product->paper_weight || $item->product->paper_finish))
                                    <p class="mt-2 text-sm text-gray-600">
                                        @if ($item->product->paper_size)
                                            Size: {{ $item->product->paper_size }}
                                        @endif

                                        @if ($item->product->paper_weight)
                                            <span class="mx-1">&middot;</span>
                                            Weight: {{ $item->product->paper_weight }}
                                        @endif

                                        @if ($item->product->paper_finish)
                                            <span class="mx-1">&middot;</span>
                                            Finish: {{ $item->product->paper_finish }}
                                        @endif
                                    </p>
                                @endif
                            </div>

                            <p class="text-sm font-medium">
                                @if (! $item->is_available)
                                    Unavailable
                                @elseif ($item->unit_price === null)
                                    Quote required
                                @else
                                    {{ number_format((float) $item->unit_price, 2) }} each
                                @endif
                            </p>
                        </div>

                        <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
                            @if ($item->is_available)
                                <form
                                    class="grid grid-cols-[auto_auto] items-end gap-x-2 gap-y-1"
                                    method="POST"
                                    action="{{ route('cart.items.update', $item) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <label
                                        class="col-span-2 text-[10px] text-gray-500"
                                        for="cart-quantity-{{ $item->id }}"
                                    >
                                        Qty
                                    </label>

                                    <div class="gz-qty-stepper gz-qty-stepper-touch gz-qty-stepper-card" data-qty-stepper>
                                        <button
                                            class="gz-qty-step"
                                            type="button"
                                            data-qty-step="-1"
                                            aria-label="Decrease quantity for {{ $item->name }}"
                                            aria-controls="cart-quantity-{{ $item->id }}"
                                        >−</button>

                                        <input
                                            class="gz-qty-input"
                                            id="cart-quantity-{{ $item->id }}"
                                            name="quantity"
                                            type="number"
                                            min="1"
                                            max="99"
                                            value="{{ $item->quantity }}"
                                            required
                                        >

                                        <button
                                            class="gz-qty-step"
                                            type="button"
                                            data-qty-step="1"
                                            aria-label="Increase quantity for {{ $item->name }}"
                                            aria-controls="cart-quantity-{{ $item->id }}"
                                        >+</button>
                                    </div>

                                    <button
                                        class="rounded border border-gray-300 px-3 py-1.5 text-sm font-medium hover:bg-white"
                                        type="submit"
                                    >
                                        Update
                                    </button>
                                </form>
                            @else
                                <p class="text-sm text-red-700">
                                    This listing is no longer available. Remove it to continue.
                                </p>
                            @endif

                            <div class="flex items-center gap-5">
                                @if ($item->is_available && $item->unit_price !== null)
                                    <p class="text-sm">
                                        Line total:
                                        <span class="font-semibold">
                                            {{ number_format(((int) round((float) $item->unit_price * 100) * $item->quantity) / 100, 2) }}
                                        </span>
                                    </p>
                                @endif

                                <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button class="text-sm text-red-700 underline" type="submit">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach

            </section>

            <aside class="h-fit border-y border-gray-200 py-5">
                <h2 class="font-semibold">Summary</h2>

                @if ($hasUnavailableItems)
                    <p class="mt-4 text-sm text-red-700">
                        Remove unavailable items before calculating a subtotal.
                    </p>
                @elseif ($requiresQuote)
                    <p class="mt-4 text-sm text-gray-600">
                        Final total requires a quote for one or more services.
                    </p>

                    <p class="mt-4 flex justify-between text-sm">
                        <span>Pre-quote subtotal</span>
                        <span class="font-semibold">{{ number_format($pricedSubtotalCents / 100, 2) }}</span>
                    </p>
                @else
                    <p class="mt-4 flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span class="font-semibold">{{ number_format($totalCents / 100, 2) }}</span>
                    </p>
                @endif

                <p class="mt-5 border-t border-gray-200 pt-4 text-xs text-gray-500">
                    Order requests are submitted without payment. Payment checkout is not available yet.
                </p>

                @if (! $hasUnavailableItems)
                    <form
                        class="mt-5 border-t border-gray-200 pt-5"
                        method="POST"
                        action="{{ route('orders.store') }}"
                        enctype="multipart/form-data"
                    >
                        @csrf

                        <label class="block text-sm font-medium" for="customer_note">
                            Note for the shop
                            <span class="font-normal text-gray-500">(optional)</span>
                        </label>

                        <textarea
                            class="mt-2 w-full rounded border border-gray-300 px-3 py-2 text-sm"
                            id="customer_note"
                            name="customer_note"
                            rows="3"
                            maxlength="5000"
                        >{{ old('customer_note') }}</textarea>

                        @error('customer_note')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror

                        <label class="mt-4 block text-sm font-medium" for="artwork">
                            Attach custom artwork
                            <span class="font-normal text-gray-500">(JPG, PNG or PDF, up to 10 MB)</span>
                        </label>

                        <input
                            class="mt-2 block w-full text-sm"
                            id="artwork"
                            name="artwork"
                            type="file"
                            accept="image/jpeg,image/png,application/pdf"
                        >

                        @error('artwork')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror

                        @error('cart')
                            <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror

                        <button class="gz-btn gz-btn-cyan mt-4 w-full" type="submit">
                            {{ $requiresQuote ? 'Request a quote' : 'Place order' }}
                        </button>
                    </form>
                @endif
            </aside>
        </div>
    @endif
@endsection
