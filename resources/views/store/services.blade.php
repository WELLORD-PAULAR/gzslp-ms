@extends('layouts.app')

@section('title', 'Services | Graphic Zone')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-emerald-800">Available services</p>
        <h1 class="mt-2 text-3xl font-semibold">Services</h1>
        <p class="mt-3 max-w-2xl text-gray-600">Printing and design services offered by our shop.</p>
    </section>

    <form class="mt-6 flex flex-wrap items-end gap-3 border-y border-gray-200 py-5" method="GET" action="{{ route('services') }}">
        <div class="min-w-48 flex-1">
            <label class="block text-sm font-medium" for="search">Search services</label>
            <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="search" name="search" value="{{ request('search') }}" type="search">
        </div>
        <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Search</button>
        <a class="px-2 py-2 text-sm underline" href="{{ route('services') }}">Clear</a>
    </form>

    @error('quantity')
        <p class="mt-5 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>
    @enderror

    <section class="mt-8 divide-y divide-gray-200 border-y border-gray-200" aria-label="Available services">
        @forelse ($services as $service)
            <article class="grid gap-3 py-6 sm:grid-cols-[1fr_auto] sm:items-start">
                <div>
                    @if ($service->image_path)<img class="mb-4 aspect-[4/3] w-full max-w-lg rounded border border-gray-200 object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ $service->name }}">@endif
                    <h2 class="text-xl font-semibold"><a class="underline decoration-transparent hover:decoration-current" href="{{ route('services.show', $service) }}">{{ $service->name }}</a></h2>
                    @if ($service->description)
                        <p class="mt-2 max-w-3xl text-gray-600">{{ $service->description }}</p>
                    @endif
                </div>
                <p class="font-medium sm:text-right">{{ $service->starting_price === null ? 'Request a quote' : 'From '.number_format((float) $service->starting_price, 2) }}</p>
                <form class="flex items-end gap-3 sm:col-span-2" method="POST" action="{{ route('cart.items.store') }}">
                    @csrf
                    <input name="item_type" type="hidden" value="service">
                    <input name="item_id" type="hidden" value="{{ $service->id }}">
                    <div>
                        <label class="block text-xs font-medium text-gray-600" for="quantity-{{ $service->id }}">Quantity</label>
                        <input class="mt-1 w-20 rounded border border-gray-300 px-2 py-1.5" id="quantity-{{ $service->id }}" name="quantity" type="number" min="1" max="99" value="1" required>
                    </div>
                    <button class="rounded bg-emerald-800 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-900" type="submit">{{ $service->starting_price === null ? 'Add for quote' : 'Add to cart' }}</button>
                </form>
            </article>
        @empty
            <p class="py-8 text-gray-600">There are no services available right now.</p>
        @endforelse
    </section>
    <div class="mt-6">{{ $services->links() }}</div>
@endsection