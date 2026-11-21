@extends('layouts.app')

@section('title', 'Order #'.$order->id.' | Graphic Zone')

@section('content')
    <section>
        <a class="text-sm underline" href="{{ route('orders.index') }}">Back to your orders</a>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase text-emerald-800">Order details</p>
                <h1 class="mt-2 text-3xl font-semibold">Order #{{ $order->id }}</h1>
                <p class="mt-2 text-sm text-gray-600">Submitted {{ $order->created_at->format('M j, Y g:i A') }}</p>
            </div>
            @include('orders._status', ['order' => $order])
        </div>
    </section>

    @if (session('status'))
        <p class="mt-5 border-l-4 border-emerald-700 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>
    @endif
    @if ($errors->has('order'))
        <p class="mt-5 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $errors->first('order') }}</p>
    @endif

    <section class="mt-8 divide-y divide-gray-200 border-y border-gray-200" aria-label="Items in this order">
        @foreach ($order->items as $item)
            <article class="flex flex-wrap justify-between gap-5 py-5">
                <div>
                    <h2 class="font-semibold">{{ $item->item_name }}</h2>
                    @if($item->variant_name)<p class="mt-1 text-sm text-gray-600">{{ $item->variant_name }}@if($item->variant_options) · {{ collect($item->variant_options)->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key).': '.$value)->join(' · ') }}@endif</p>@endif
                    @if ($item->item_description)<p class="mt-1 text-sm text-gray-600">{{ $item->item_description }}</p>@endif
                    @if ($item->paper_size || $item->paper_weight || $item->paper_finish)
                        <p class="mt-2 text-sm text-gray-600">{{ collect(['Size: '.$item->paper_size, 'Weight: '.$item->paper_weight, 'Finish: '.$item->paper_finish])->filter(fn ($detail) => ! str_ends_with($detail, ': '))->join(' · ') }}</p>
                    @endif
                    <p class="mt-2 text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                </div>
                <p class="font-medium">{{ $item->unit_price === null ? 'Quote required' : number_format((float) $item->line_total, 2) }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 ml-auto max-w-sm border-y border-gray-200 py-5">
        @if ($order->status === \App\Models\Order::STATUS_QUOTE_REQUESTED)
            <p class="text-sm text-gray-600">The shop is preparing a quote. Your pre-quote subtotal is {{ number_format((float) $order->subtotal, 2) }}.</p>
        @else
            <p class="flex justify-between font-semibold"><span>{{ $order->status === \App\Models\Order::STATUS_QUOTE_SENT ? 'Quoted total' : 'Order total' }}</span><span>{{ number_format((float) $order->current_total, 2) }}</span></p>
        @endif
        @if ($order->customer_note)<p class="mt-4 text-sm text-gray-600"><span class="font-medium">Your note:</span> {{ $order->customer_note }}</p>@endif
        @if ($order->admin_note)<p class="mt-3 text-sm text-gray-600"><span class="font-medium">Shop update:</span> {{ $order->admin_note }}</p>@endif
        @if ($order->artwork_path)<p class="mt-3 text-sm"><a class="font-medium underline" href="{{ route('orders.artwork', $order) }}">Download your submitted artwork</a></p>@endif
    </section>

    @if ($order->canBeCancelledByCustomer())
        <div class="mt-6 flex flex-wrap gap-3">
            @if ($order->status === \App\Models\Order::STATUS_QUOTE_SENT)
                <form method="POST" action="{{ route('orders.accept-quote', $order) }}" onsubmit="return window.confirm('Accept this quote and start order processing?')">
                    @csrf
                    @method('PATCH')
                    <button class="rounded bg-emerald-800 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-900" type="submit">Accept quote</button>
                </form>
            @endif
            <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return window.confirm('Cancel this order?')">
                @csrf
                @method('PATCH')
                <button class="rounded border border-red-300 px-4 py-2 text-sm font-medium text-red-800 hover:bg-red-50" type="submit">Cancel order</button>
            </form>
        </div>
    @endif
@endsection