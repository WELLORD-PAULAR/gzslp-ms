@extends('layouts.app')

@section('title', $service->name.' | Print Studio')

@section('content')
    <section class="max-w-3xl">
        <a class="text-sm underline" href="{{ route('services') }}">Back to services</a>
        <p class="mt-5 text-sm font-semibold uppercase text-emerald-800">Printing service</p>
        <h1 class="mt-2 text-3xl font-semibold">{{ $service->name }}</h1>
        @if ($service->image_path)<img class="mt-6 aspect-[4/3] w-full max-w-2xl rounded border border-gray-200 object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ $service->name }}">@endif
        @if ($service->description)<p class="mt-4 text-gray-600">{{ $service->description }}</p>@endif
        <p class="mt-6 border-y border-gray-200 py-5 text-lg font-semibold">{{ $service->starting_price === null ? 'Price provided by quote' : 'Starting at '.number_format((float) $service->starting_price, 2) }}</p>
        @error('quantity')<p class="mt-4 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
        <form class="mt-6 flex items-end gap-3" method="POST" action="{{ route('cart.items.store') }}">
            @csrf
            <input name="item_type" type="hidden" value="service">
            <input name="item_id" type="hidden" value="{{ $service->id }}">
            <div><label class="block text-sm font-medium" for="quantity">Quantity</label><input class="mt-1 w-24 rounded border border-gray-300 px-3 py-2" id="quantity" name="quantity" type="number" min="1" max="99" value="1" required></div>
            <button class="rounded bg-emerald-800 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-900" type="submit">{{ $service->starting_price === null ? 'Request a quote' : 'Add to cart' }}</button>
        </form>
    </section>
@endsection