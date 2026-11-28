@extends('layouts.admin')

@section('title', 'Reports | Admin')

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Pending',
            'quote_requested' => 'Quote requested',
            'quote_sent' => 'Quote sent',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <section class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase text-gray-500">Reporting</p>
            <h1 class="mt-2 text-3xl font-semibold">Order activity</h1>
            <p class="mt-2 text-sm text-gray-600">Completed order value reflects priced orders marked complete in the selected period.</p>
        </div>
        <button class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium print:hidden" type="button" onclick="window.print()">Print report</button>
    </section>

    <form class="mt-7 flex flex-wrap items-end gap-4 border-y border-gray-200 py-5 print:hidden" method="GET" action="{{ route('admin.reports.index') }}">
        <div>
            <label class="block text-sm font-medium" for="date_from">From</label>
            <input class="mt-1 rounded border border-gray-300 px-3 py-2" id="date_from" name="date_from" type="date" value="{{ $dateFrom->toDateString() }}">
        </div>
        <div>
            <label class="block text-sm font-medium" for="date_to">To</label>
            <input class="mt-1 rounded border border-gray-300 px-3 py-2" id="date_to" name="date_to" type="date" value="{{ $dateTo->toDateString() }}">
        </div>
        <div>
            <label class="block text-sm font-medium" for="per_page">Rows per page</label>
            <select class="mt-1 rounded border border-gray-300 px-3 py-2" id="per_page" name="per_page">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected((int) request('per_page', 25) === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Apply filters</button>
        <a class="px-2 py-2 text-sm underline" href="{{ route('admin.reports.index') }}">This month</a>
    </form>

    @if ($errors->any())
        <div class="mt-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</div>
    @endif

    <p class="mt-5 text-sm text-gray-600">Report period: {{ $dateFrom->format('M j, Y') }} – {{ $dateTo->format('M j, Y') }}</p>

    <section class="mt-5 grid gap-5 border-y border-gray-200 py-6 sm:grid-cols-2 lg:grid-cols-4" aria-label="Order summary">
        <div><p class="text-sm text-gray-500">Orders submitted</p><p class="mt-1 text-2xl font-semibold">{{ $statusCounts->sum() }}</p></div>
        <div><p class="text-sm text-gray-500">Completed orders</p><p class="mt-1 text-2xl font-semibold">{{ $completedOrderCount }}</p></div>
<<<<<<< HEAD
        <div><p class="text-sm text-gray-500">Completed order value</p><p class="mt-1 text-2xl font-semibold">{{ number_format((float) $completedOrderValue, 2) }}</p><p class="mt-1 text-xs text-gray-500">Not payment-verified revenue</p></div>
        <div><p class="text-sm text-gray-500">Orders requiring a quote</p><p class="mt-1 text-2xl font-semibold">{{ $quoteRequestCount }}</p><p class="mt-1 text-xs text-gray-500">Includes quote-sent, accepted, and cancelled quote orders.</p></div>
=======
        <div><p class="text-sm text-gray-500">Completed order value</p><p class="mt-1 text-2xl font-semibold">{{ number_format((float) $completedOrderValue, 2) }}</p><p class="mt-1 text-xs text-gray-500">Based on completed orders</p></div>
        <div><p class="text-sm text-gray-500">Quote requests</p><p class="mt-1 text-2xl font-semibold">{{ $statusCounts->get('quote_requested', 0) }}</p></div>
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
    </section>

    <section class="mt-8 grid gap-8 lg:grid-cols-[16rem_1fr]">
        <div>
            <h2 class="font-semibold">Orders by status</h2>
            <dl class="mt-3 divide-y divide-gray-200 border-y border-gray-200 text-sm">
                @foreach ($statusLabels as $status => $label)
                    <div class="flex justify-between gap-4 py-2"><dt>{{ $label }}</dt><dd class="font-medium">{{ $statusCounts->get($status, 0) }}</dd></div>
                @endforeach
            </dl>

            <h2 class="mt-8 font-semibold">Catalog visibility</h2>
            <dl class="mt-3 divide-y divide-gray-200 border-y border-gray-200 text-sm">
                <div class="flex justify-between gap-4 py-2"><dt>Active products</dt><dd class="font-medium">{{ $catalogCounts['active_products'] }}</dd></div>
                <div class="flex justify-between gap-4 py-2"><dt>Hidden products</dt><dd class="font-medium">{{ $catalogCounts['hidden_products'] }}</dd></div>
                <div class="flex justify-between gap-4 py-2"><dt>Active services</dt><dd class="font-medium">{{ $catalogCounts['active_services'] }}</dd></div>
                <div class="flex justify-between gap-4 py-2"><dt>Hidden services</dt><dd class="font-medium">{{ $catalogCounts['hidden_services'] }}</dd></div>
            </dl>
<<<<<<< HEAD
            <p class="mt-3 text-xs text-gray-500">This is catalog visibility, not stock inventory. Current quantities are tracked on the Inventory screen.</p>
=======
            <p class="mt-3 text-xs text-gray-500">This is catalog visibility, not stock inventory. Stock levels are tracked in the inventory workspace.</p>
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
        </div>

        <div>
            <h2 class="font-semibold">Orders in period</h2>
            <div class="mt-3 overflow-x-auto border-y border-gray-200 bg-white">
                <table class="w-full min-w-[38rem] text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-500">
                        <tr><th class="px-3 py-3 font-medium">Order</th><th class="px-3 py-3 font-medium">Customer</th><th class="px-3 py-3 font-medium">Date</th><th class="px-3 py-3 font-medium">Status</th><th class="px-3 py-3 text-right font-medium">Recorded value</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-3 py-3"><a class="underline" href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a></td>
                                <td class="px-3 py-3">{{ $order->user->name }}</td>
                                <td class="px-3 py-3">{{ $order->created_at->format('M j, Y') }}</td>
                                <td class="px-3 py-3">{{ $statusLabels[$order->status] ?? $order->status }}</td>
                                <td class="px-3 py-3 text-right">{{ $order->requiresQuote() && $order->current_total === null ? 'Quote pending' : number_format((float) $order->current_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-3 py-8 text-center text-gray-500" colspan="5">No orders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4 print:hidden">
                {{ $orders->links() }}
                @include('partials.pagination-status', ['paginator' => $orders])
            </div>
        </div>
    </section>
@endsection