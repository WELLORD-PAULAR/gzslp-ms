@extends('layouts.app')

@section('title', 'Services | Print Studio')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-emerald-800">Available services</p>
        <h1 class="mt-2 text-3xl font-semibold">Services</h1>
        <p class="mt-3 max-w-2xl text-gray-600">Printing and design services offered by our shop.</p>
    </section>

    <section class="mt-8 divide-y divide-gray-200 border-y border-gray-200" aria-label="Available services">
        @forelse ($services as $service)
            <article class="grid gap-3 py-6 sm:grid-cols-[1fr_auto] sm:items-start">
                <div>
                    <h2 class="text-xl font-semibold">{{ $service->name }}</h2>
                    @if ($service->description)
                        <p class="mt-2 max-w-3xl text-gray-600">{{ $service->description }}</p>
                    @endif
                </div>
                <p class="font-medium sm:text-right">{{ $service->starting_price === null ? 'Request a quote' : 'From '.number_format((float) $service->starting_price, 2) }}</p>
            </article>
        @empty
            <p class="py-8 text-gray-600">There are no services available right now.</p>
        @endforelse
    </section>
@endsection