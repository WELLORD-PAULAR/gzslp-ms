@extends('layouts.admin')

@section('title', 'Orders | Admin')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-gray-500">Order management</p>
        <h1 class="mt-2 text-3xl font-semibold">Customer orders</h1>
    </section>

    <div class="mt-7 overflow-x-auto border-y border-gray-200 bg-white">
        <table class="w-full min-w-[42rem] text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-500">
                <tr><th class="px-4 py-3 font-medium">Order</th><th class="px-4 py-3 font-medium">Customer</th><th class="px-4 py-3 font-medium">Submitted</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Payment</th><th class="px-4 py-3 text-right font-medium">Total</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3"><a class="font-medium underline" href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a></td>
                        <td class="px-4 py-3">{{ $order->user->name }}<span class="mt-1 block text-gray-500">{{ $order->user->email }}</span></td>
                        <td class="px-4 py-3">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3">@include('orders._status', ['order' => $order])</td>
                        <td class="px-4 py-3">
                            @if (! $order->hasFinalTotal())
                                <span class="text-gray-500">Quote pending</span>
                            @elseif ($order->isPaid())
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-900">Paid</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-900">Due {{ number_format($order->amountDueCents() / 100, 2) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">{{ $order->current_total === null ? 'Quote pending' : number_format((float) $order->current_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-gray-500" colspan="6">No orders have been submitted.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $orders->links() }}</div>
@endsection