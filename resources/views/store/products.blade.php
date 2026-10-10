@extends('layouts.app')

@section('title', 'Products | Print Studio')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-emerald-800">Shop the catalog</p>
        <h1 class="mt-2 text-3xl font-semibold">Printed products</h1>
        <p class="mt-3 max-w-2xl text-gray-600">Browse the products currently available from our print shop.</p>
    </section>

    <section class="mt-8 grid gap-x-8 sm:grid-cols-2" aria-label="Available products">
        @forelse ($products as $product)
            <article class="border-y border-gray-200 py-6">
                <h2 class="text-xl font-semibold">{{ $product->name }}</h2>
                @if ($product->description)
                    <p class="mt-2 text-gray-600">{{ $product->description }}</p>
                @endif
                @if ($product->paper_size || $product->paper_weight || $product->paper_finish)
                    <p class="mt-3 text-sm text-gray-600">
                        @if ($product->paper_size) Size: {{ $product->paper_size }} @endif
                        @if ($product->paper_weight) <span class="mx-1">&middot;</span> Weight: {{ $product->paper_weight }} @endif
                        @if ($product->paper_finish) <span class="mx-1">&middot;</span> Finish: {{ $product->paper_finish }} @endif
                    </p>
                @endif
                <p class="mt-4 font-medium">{{ number_format((float) $product->price, 2) }}</p>
            </article>
        @empty
            <p class="border-y border-gray-200 py-8 text-gray-600">There are no products available right now.</p>
        @endforelse
    </section>
@endsection