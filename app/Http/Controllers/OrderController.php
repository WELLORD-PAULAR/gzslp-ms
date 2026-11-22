<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Services\InventoryConsumptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->with('items')->latest()->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $artworkPath = $request->file('artwork')?->store('order-artwork', 'local');

        try {
            $order = DB::transaction(function () use ($request, $artworkPath): Order {
            $cart = Cart::query()
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                throw ValidationException::withMessages(['cart' => 'Add items to your cart before submitting an order.']);
            }

            $items = $cart->items()->with(['product', 'productVariant', 'service'])->lockForUpdate()->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Add items to your cart before submitting an order.']);
            }

            if ($items->contains(fn (CartItem $item): bool => ! $item->is_available)) {
                throw ValidationException::withMessages(['cart' => 'Your cart contains an unavailable item. Remove it before submitting the order.']);
            }

            $requiresQuote = $items->contains(fn (CartItem $item): bool => $item->unit_price === null);
            $subtotalCents = $items
                ->filter(fn (CartItem $item): bool => $item->unit_price !== null)
                ->sum(fn (CartItem $item): int => (int) round((float) $item->unit_price * 100) * $item->quantity);

            $order = $request->user()->orders()->create([
                'status' => $requiresQuote ? Order::STATUS_QUOTE_REQUESTED : Order::STATUS_PENDING,
                'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                'customer_note' => $request->validated('customer_note'),
                'artwork_path' => $artworkPath,
            ]);

            foreach ($items as $item) {
                $product = $item->product;
                $service = $item->service;

                if ((bool) $product === (bool) $service) {
                    throw ValidationException::withMessages(['cart' => 'A cart item must reference exactly one product or service.']);
                }

                $order->items()->create([
                    'product_id' => $product?->id,
                    'product_variant_id' => $item->product_variant_id,
                    'service_id' => $service?->id,
                    'item_name' => $product?->name ?? $service->name,
                    'item_description' => $product?->description ?? $service?->description,
                    'variant_name' => $item->productVariant?->name,
                    'variant_options' => $item->productVariant?->options,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'paper_size' => $product?->paper_size,
                    'paper_weight' => $product?->paper_weight,
                    'paper_finish' => $product?->paper_finish,
                ]);
            }

            $cart->items()->delete();

            // A priced order is a confirmed purchase: take the supplies now, in the same
            // transaction, so a shortage rejects the whole checkout and nothing is saved.
            // Quote orders take their supplies when the customer accepts the quote.
            if (! $requiresQuote) {
                try {
                    app(InventoryConsumptionService::class)->consumeForOrder($order, $request->user());
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages([
                        'cart' => $exception->errors()['inventory'] ?? ['Some items in your cart cannot be supplied right now.'],
                    ]);
                }
            }

            return $order;
            });
        } catch (Throwable $exception) {
            if ($artworkPath !== null) {
                Storage::disk('local')->delete($artworkPath);
            }

            throw $exception;
        }

        return redirect()->route('orders.show', $order)->with('status', 'Your order has been submitted.');
    }

    public function show(Request $request, Order $order): View
    {
        $order = $request->user()->orders()->with('items')->findOrFail($order->id);

        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $order = $request->user()->orders()->findOrFail($order->id);

        $cancelled = DB::transaction(function () use ($request, $order): bool {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $lockedOrder->canBeCancelledByCustomer()) {
                return false;
            }

            app(InventoryConsumptionService::class)->restoreForOrder($lockedOrder, $request->user());
            $lockedOrder->update(['status' => Order::STATUS_CANCELLED]);

            return true;
        });

        if (! $cancelled) {
            return redirect()->route('orders.show', $order)->withErrors([
                'order' => 'This order can no longer be cancelled online. Contact the shop if you need help.',
            ]);
        }

        return redirect()->route('orders.show', $order)->with('status', 'Your order was cancelled.');
    }

    public function acceptQuote(Request $request, Order $order): RedirectResponse
    {
        $order = $request->user()->orders()->findOrFail($order->id);

        try {
            DB::transaction(function () use ($request, $order): void {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($lockedOrder->status !== Order::STATUS_QUOTE_SENT || $lockedOrder->quoted_total === null) {
                    throw ValidationException::withMessages([
                        'order' => 'There is no quote available to accept for this order.',
                    ]);
                }

                app(InventoryConsumptionService::class)->consumeForOrder($lockedOrder, $request->user());
                $lockedOrder->update([
                    'status' => Order::STATUS_PROCESSING,
                    'customer_accepted_at' => now(),
                ]);
            });
        } catch (ValidationException $exception) {
            return redirect()->route('orders.show', $order)->withErrors($exception->errors());
        }

        return redirect()->route('orders.show', $order)->with('status', 'Quote accepted. Your order is now processing.');
    }

    public function downloadArtwork(Request $request, Order $order): StreamedResponse
    {
        $order = $request->user()->orders()->findOrFail($order->id);

        abort_if($order->artwork_path === null, 404);

        return Storage::disk('local')->download($order->artwork_path, 'order-'.$order->id.'-artwork');
    }
}