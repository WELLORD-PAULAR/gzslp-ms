@extends('layouts.admin')

@section('title', 'Order #'.$order->id.' | Admin')

@section('content')
    <section>
        <a class="text-sm underline" href="{{ route('admin.orders.index') }}">Back to orders</a>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-5">
            <div>
                <p class="text-sm font-semibold uppercase text-gray-500">Order management</p>
                <h1 class="mt-2 text-3xl font-semibold">Order #{{ $order->id }}</h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $order->user->name }} &middot;
                    {{ $order->user->email }} &middot;
                    {{ $order->created_at->format('M j, Y g:i A') }}
                </p>
            </div>

            @can('access-admin')
                @include('orders._status', ['order' => $order])
            @endcan
        </div>
    </section>

    @if (session('status'))
        <p class="mt-5 border-l-4 border-cyan-500 bg-white px-4 py-3 text-sm" role="status">
            {{ session('status') }}
        </p>
    @endif

    <section class="mt-8 divide-y divide-gray-200 border-y border-gray-200" aria-label="Order items">
        @foreach ($order->items as $item)
            <article class="grid items-start gap-5 py-5 sm:grid-cols-[1fr_auto]">
                <div>
                    <h2 class="font-semibold">
                        {{ $item->item_name }}
                        <span class="font-normal text-gray-500">&times; {{ $item->quantity }}</span>
                    </h2>

                    @if ($item->variant_name)
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $item->variant_name }}
                            @if ($item->variant_options)
                                &middot;
                                {{ collect($item->variant_options)->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key).': '.$value)->join(' &middot; ') }}
                            @endif
                        </p>
                    @endif

                    @if ($item->item_description)
                        <p class="mt-1 text-sm text-gray-600">{{ $item->item_description }}</p>
                    @endif

                    @if ($item->paper_size || $item->paper_weight || $item->paper_finish)
                        <p class="mt-2 text-sm text-gray-600">
                            {{ collect([
                                'Size: '.$item->paper_size,
                                'Weight: '.$item->paper_weight,
                                'Finish: '.$item->paper_finish,
                            ])->filter(fn ($detail) => ! str_ends_with($detail, ': '))->join(' &middot; ') }}
                        </p>
                    @endif
                </div>

                <p class="text-sm font-medium">
                    {{ $item->unit_price === null
                        ? ($order->requiresQuote() ? 'Quote required' : 'Included in quote')
                        : number_format((float) $item->line_total, 2) }}
                </p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 ml-auto max-w-md border-y border-gray-200 py-5">
        @if ($order->requiresQuote())
            <p class="flex justify-between text-sm">
                <span>Pre-quote subtotal</span>
                <span class="font-semibold">{{ number_format((float) $order->subtotal, 2) }}</span>
            </p>

            @if ($order->quoted_total !== null)
                <p class="mt-2 flex justify-between text-sm">
                    <span>Quoted total</span>
                    <span class="font-semibold">{{ number_format((float) $order->quoted_total, 2) }}</span>
                </p>
            @endif
        @else
            <p class="flex justify-between text-sm">
                <span>Order total</span>
                <span class="font-semibold">{{ number_format((float) $order->current_total, 2) }}</span>
            </p>
        @endif

        @if ($order->customer_note)
            <p class="mt-4 text-sm text-gray-600">
                <span class="font-medium">Customer note:</span> {{ $order->customer_note }}
            </p>
        @endif

        @if ($order->artwork_path)
            <p class="mt-3 text-sm">
                <a class="font-medium underline" href="{{ route('admin.orders.artwork', $order) }}">
                    Download customer artwork
                </a>
            </p>
        @endif
    </section>

    @can('access-admin')
        @if ($allowedStatuses)
            <form class="mt-8 max-w-xl space-y-4 border-y border-gray-200 py-6"
                  method="POST"
                  action="{{ route('admin.orders.update', $order) }}"
                  onsubmit="return window.confirm('Save this order update?')">
                @csrf
                @method('PATCH')

                <h2 class="text-lg font-semibold">Update order</h2>

                <div>
                    <label class="block text-sm font-medium" for="status">Next status</label>
                    <select class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                            id="status" name="status" required>
                        <option value="">Choose a status</option>
                        @foreach ($allowedStatuses as $status)
                            <option value="{{ $status }}" @selected(old('status') === $status)>
                                {{ \Illuminate\Support\Str::headline($status) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                @if (in_array($order->status, [
                    \App\Models\Order::STATUS_QUOTE_REQUESTED,
                    \App\Models\Order::STATUS_QUOTE_SENT,
                ], true))
                    <div>
                        <label class="block text-sm font-medium" for="quoted_total">
                            Quoted total
                            <span class="font-normal text-gray-500">(required when sending a quote)</span>
                        </label>
                        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                               id="quoted_total" name="quoted_total"
                               type="number" min="0.01" max="99999999.99" step="0.01"
                               value="{{ old('quoted_total', $order->quoted_total) }}">
                        @error('quoted_total')
                            <p class="text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium" for="admin_note">Update for customer</label>
                    <textarea class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                              id="admin_note" name="admin_note" rows="3" maxlength="5000">{{ old('admin_note', $order->admin_note) }}</textarea>
                    @error('admin_note')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">
                    Save update
                </button>
            </form>
        @endif
    @endcan
@endsection
