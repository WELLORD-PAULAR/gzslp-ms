@extends('layouts.app')

@section('title', 'Products | Print Studio')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-emerald-800">Shop the catalog</p>
        <h1 class="mt-2 text-3xl font-semibold">Printed products</h1>
        <p class="mt-3 max-w-2xl text-gray-600">Browse the products currently available from our print shop.</p>
    </section>

    <form class="mt-6 flex flex-wrap items-end gap-3 border-y border-gray-200 py-5" method="GET" action="{{ route('products.index') }}">
        <div class="min-w-48 flex-1">
            <label class="block text-sm font-medium" for="search">Search products</label>
            <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="search" name="search" value="{{ request('search') }}" type="search">
        </div>
        <div>
            <label class="block text-sm font-medium" for="paper_size">Paper size</label>
            <select class="mt-1 rounded border border-gray-300 px-3 py-2" id="paper_size" name="paper_size">
                <option value="">All sizes</option>
                @foreach ($paperSizes as $paperSize)<option value="{{ $paperSize }}" @selected(request('paper_size') === $paperSize)>{{ $paperSize }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium" for="paper_finish">Finish</label>
            <select class="mt-1 rounded border border-gray-300 px-3 py-2" id="paper_finish" name="paper_finish">
                <option value="">All finishes</option>
                @foreach ($paperFinishes as $paperFinish)<option value="{{ $paperFinish }}" @selected(request('paper_finish') === $paperFinish)>{{ $paperFinish }}</option>@endforeach
            </select>
        </div>
        <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Filter</button>
        <a class="px-2 py-2 text-sm underline" href="{{ route('products.index') }}">Clear</a>
    </form>

    @error('quantity')
        <p class="mt-5 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>
    @enderror

    <section class="mt-8 grid gap-x-8 sm:grid-cols-2" aria-label="Available products">
        @forelse ($products as $product)
            <article class="border-y border-gray-200 py-6">
                @if ($product->image_path)<img class="mb-4 aspect-[4/3] w-full max-w-lg rounded border border-gray-200 object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">@endif
                <h2 class="text-xl font-semibold"><a class="underline decoration-transparent hover:decoration-current" href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h2>
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
                <p class="mt-4 font-medium">From {{ number_format((float) $product->variants->min('price'), 2) }}</p>
                <a class="mt-4 inline-flex rounded border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-white" href="{{ route('products.show', $product) }}">Choose options</a>
            </article>
        @empty
            <p class="border-y border-gray-200 py-8 text-gray-600">There are no products available right now.</p>
        @endforelse
    </section>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection