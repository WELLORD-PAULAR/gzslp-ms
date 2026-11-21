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
<span class="inline-flex rounded border border-gray-300 px-2 py-1 text-xs font-medium">{{ $statusLabels[$order->status] ?? \Illuminate\Support\Str::headline($order->status) }}</span>