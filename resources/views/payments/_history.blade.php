@php($payments ??= $order->payments()->latest()->get())
<section class="mt-8 border-y border-gray-200 py-6">
    <h2 class="text-lg font-semibold">Payment history</h2>
    @if ($payments->isEmpty())
        <p class="mt-3 text-sm text-gray-500">No payments have been recorded for this order.</p>
    @else
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[34rem] text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Reference</th>
                        <th class="px-3 py-2 font-medium">Method</th>
                        <th class="px-3 py-2 font-medium">Amount</th>
                        <th class="px-3 py-2 font-medium">Status</th>
                        <th class="px-3 py-2 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($payments as $payment)
                        <tr>
                            <td class="px-3 py-3 font-mono text-xs">{{ $payment->reference ?: '—' }}</td>
                            <td class="px-3 py-3">{{ \Illuminate\Support\Str::headline($payment->method) }}</td>
                            <td class="px-3 py-3 font-medium">{{ $payment->formattedAmount() }} {{ $payment->currency }}</td>
                            <td class="px-3 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-bold {{ $payment->isSuccessful() ? 'bg-emerald-100 text-emerald-900' : ($payment->isFailed() ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-900') }}">{{ \Illuminate\Support\Str::headline($payment->status) }}</span>
                            </td>
                            <td class="px-3 py-3 text-gray-500">{{ $payment->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        @if ($payment->isFailed() && $payment->failure_reason)
                            <tr><td class="px-3 py-2 text-xs text-red-700" colspan="5">{{ $payment->failure_reason }}</td></tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
