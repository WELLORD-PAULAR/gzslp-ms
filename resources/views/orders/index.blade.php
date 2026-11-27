@extends('layouts.app')

@section('title', 'Your orders | Graphic Zone')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-[#008da5]">Customer account</p>
        <h1 class="mt-2 text-3xl font-semibold">Your orders</h1>
    </section>

    @if (session('status'))
        <p class="mt-5 border-l-4 border-cyan-500 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>
    @endif

    <section class="mt-7 divide-y divide-gray-200 border-y border-gray-200" aria-label="Order history">
        @forelse ($orders as $order)
            <a class="grid gap-3 py-5 sm:grid-cols-[1fr_auto_auto] sm:items-center" href="{{ route('orders.show', $order) }}">
                <span>
                    <span class="block font-semibold">Order #{{ $order->id }}</span>
                    <span class="mt-1 block text-sm text-gray-600">{{ $order->created_at->format('M j, Y g:i A') }} · {{ $order->items->sum('quantity') }} item(s)</span>
                    <span class="mt-1 block text-sm text-gray-600">{{ $order->items->pluck('item_name')->join(', ') }}</span>
                </span>
                <span>@include('orders._status', ['order' => $order])</span>
                <span class="text-sm font-medium sm:text-right">{{ $order->requiresQuote() && $order->current_total === null ? 'Quote pending' : number_format((float) $order->current_total, 2) }}</span>
            </a>
        @empty
            <p class="py-8 text-gray-600">You have not submitted an order yet.</p>
        @endforelse
    </section>

    <div class="mt-5">{{ $orders->links() }}</div>
@endsection