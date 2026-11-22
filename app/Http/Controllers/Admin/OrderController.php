<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\InventoryConsumptionService;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['user', 'items', 'payments'])->latest()->paginate(20);

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

        try {
            DB::transaction(function () use ($request, $order, $data): void {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

                if (! in_array($data['status'], $lockedOrder->allowedAdminStatuses(), true)) {
                    throw ValidationException::withMessages(['status' => 'This order status has changed. Reload and choose an available next status.']);
                }

                if ($data['status'] === Order::STATUS_PROCESSING) {
                    app(InventoryConsumptionService::class)->consumeForOrder($lockedOrder, $request->user());
                }

                if ($data['status'] === Order::STATUS_CANCELLED) {
                    app(InventoryConsumptionService::class)->restoreForOrder($lockedOrder, $request->user());
                }

                if ($data['status'] === Order::STATUS_QUOTE_SENT) {
                    $lockedOrder->quoted_total = $data['quoted_total'];
                }

                $lockedOrder->status = $data['status'];
                $lockedOrder->admin_note = $data['admin_note'] ?? null;
                $lockedOrder->save();
            });
        } catch (ValidationException $exception) {
            return redirect()->route('admin.orders.show', $order)->withErrors($exception->errors());
        }

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order updated.');
    }

    public function downloadArtwork(Order $order): StreamedResponse
    {
        abort_if($order->artwork_path === null, 404);

        return Storage::disk('local')->download($order->artwork_path, 'order-'.$order->id.'-artwork');
    }
}