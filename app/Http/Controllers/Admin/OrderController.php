<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['user', 'items'])->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items']);
        $allowedStatuses = $order->allowedAdminStatuses();

        return view('admin.orders.show', compact('order', 'allowedStatuses'));
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === Order::STATUS_QUOTE_SENT) {
            $order->quoted_total = $data['quoted_total'];
        }

        $order->status = $data['status'];
        $order->admin_note = $data['admin_note'] ?? null;
        $order->save();

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order updated.');
    }

    public function downloadArtwork(Order $order): StreamedResponse
    {
        abort_if($order->artwork_path === null, 404);

        return Storage::disk('local')->download($order->artwork_path, 'order-'.$order->id.'-artwork');
    }
}