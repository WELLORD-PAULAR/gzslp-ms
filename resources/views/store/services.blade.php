@extends('layouts.app')

@section('title', 'Services | Graphic Zone')

@section('content')
    <section>
        <p class="gz-eyebrow">Available services</p>
        <h1 class="mt-1 text-2xl font-bold">Services</h1>
        <p class="mt-1 text-sm text-gray-600">Printing and design services offered by our shop.</p>
    </section>

    <form class="mt-5 flex flex-wrap items-end gap-2 border-y border-gray-200 py-4" method="GET" action="{{ route('services') }}">
        <div class="min-w-40 flex-1">
            <label class="block text-xs font-medium text-gray-600" for="search">Search services</label>
            <input class="mt-1 w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm" id="search" name="search" value="{{ request('search') }}" type="search">
        </div>
        <button class="rounded bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white" type="submit">Search</button>
        <a class="px-2 py-1.5 text-xs underline" href="{{ route('services') }}">Clear</a>
    </form>

    @error('quantity')
        <p class="mt-4 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>
    @enderror

    <section class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6" aria-label="Available services">
        @forelse ($services as $service)
            <article class="flex flex-col overflow-hidden rounded border border-gray-200 bg-white transition hover:border-emerald-700 hover:shadow-sm">
                <a class="block bg-gray-50" href="{{ route('services.show', $service) }}">
                    @if ($service->image_path)
                        <img class="aspect-square w-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ $service->name }}">
                    @else
                        <span class="block aspect-square w-full bg-gray-100"></span>
                    @endif
                </a>
                <div class="flex flex-1 flex-col gap-1 p-2">
                    <h2 class="line-clamp-2 text-xs leading-snug text-gray-800"><a class="hover:text-emerald-800 hover:underline" href="{{ route('services.show', $service) }}">{{ $service->name }}</a></h2>
                    @if ($service->description)
                        <p class="line-clamp-2 text-[10px] leading-snug text-gray-500">{{ $service->description }}</p>
                    @endif
                    <p class="mt-auto pt-1 text-sm font-bold text-[#008da5]">{{ $service->starting_price === null ? 'Request a quote' : 'From '.number_format((float) $service->starting_price, 2) }}</p>
                    <form class="mt-1 grid grid-cols-[auto_minmax(0,1fr)] items-end gap-1" method="POST" action="{{ route('cart.items.store') }}">
                        @csrf
                        <input name="item_type" type="hidden" value="service">
                        <input name="item_id" type="hidden" value="{{ $service->id }}">
                        <label class="text-[10px] text-gray-500" for="quantity-{{ $service->id }}">Qty</label>
                        <input class="w-full rounded border border-gray-300 px-2 py-1 text-center text-sm" id="quantity-{{ $service->id }}" name="quantity" type="number" min="1" max="99" value="1" required>
                        <button class="col-span-2 rounded bg-[#008da5] px-1.5 py-0.5 text-[9px] font-semibold leading-tight text-white hover:bg-[#006f83]" type="submit">{{ $service->starting_price === null ? 'Add for quote' : 'Add to cart' }}</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="col-span-full rounded border border-dashed border-gray-300 py-8 text-center text-sm text-gray-500">There are no services available right now.</p>
        @endforelse
    </section>
    <div class="mt-5">{{ $services->links() }}</div>
@endsection
