@php
    $orderTotal = $order->current_total;
    $payments = $order->payments()->latest()->get();
@endphp
@if ($order->hasFinalTotal() && $order->status !== \App\Models\Order::STATUS_CANCELLED)
    <section class="mt-8 border-y border-gray-200 py-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Payment</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Total {{ number_format((float) $orderTotal, 2) }} ·
                    Paid {{ number_format($order->paidAmountCents() / 100, 2) }} ·
                    Balance {{ number_format($order->amountDueCents() / 100, 2) }}
                </p>
            </div>
            @if ($order->isPaid())
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-emerald-900">Paid in full</span>
            @endif
        </div>

        @error('payment')<p class="mt-4 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror

        @if ($order->needsPayment())
            <form class="mt-5 max-w-md rounded-xl border border-gray-200 bg-white p-5" method="POST" action="{{ route('orders.payment.store', $order) }}">
                @csrf
                <label class="block text-sm font-medium" for="payment-method">Payment method</label>
                <select class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="payment-method" name="method" required>
                    @foreach (config('payments.methods') as $value => $label)
                        <option value="{{ $value }}" @selected(old('method') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('method')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                <button class="gz-btn gz-btn-primary mt-4" type="submit">Pay {{ number_format($order->amountDueCents() / 100, 2) }}</button>
                <p class="mt-2 text-xs text-gray-500">Demo gateway — no real charge is made.</p>
            </form>
        @endif
    </section>
@endif

@include('payments._history', ['order' => $order, 'payments' => $payments])
