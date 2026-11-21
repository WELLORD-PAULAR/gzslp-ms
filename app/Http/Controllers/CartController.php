<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use App\Services\InventoryAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->user()->cart()->with(['items.product', 'items.productVariant', 'items.service'])->first();
        $items = $cart?->items ?? collect();
        $hasUnavailableItems = $items->contains(fn (CartItem $item): bool => ! $item->is_available);
        $requiresQuote = $items->contains(fn (CartItem $item): bool => $item->is_available && $item->unit_price === null);
        $pricedSubtotalCents = $items
            ->filter(fn (CartItem $item): bool => $item->is_available && $item->unit_price !== null)
            ->sum(fn (CartItem $item): int => (int) round((float) $item->unit_price * 100) * $item->quantity);
        $totalCents = null;

        if (! $hasUnavailableItems && ! $requiresQuote) {
            $totalCents = $pricedSubtotalCents;
        }

        return view('cart.index', compact('items', 'totalCents', 'pricedSubtotalCents', 'hasUnavailableItems', 'requiresQuote'));
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $itemType = $data['item_type'];
        $catalogItem = $itemType === 'product'
            ? Product::where('is_active', true)->findOrFail($data['item_id'])
            : Service::where('is_active', true)->findOrFail($data['item_id']);
        $variant = $itemType === 'product'
            ? $catalogItem->variants()->where('is_active', true)->findOrFail($data['variant_id'])
            : null;
        $cart = $request->user()->cart()->firstOrCreate();

        DB::transaction(function () use ($cart, $data, $itemType, $catalogItem, $variant): void {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $itemQuery = $lockedCart->items();

            if ($itemType === 'product') {
                $itemQuery->where('product_variant_id', $variant->id);
            } else {
                $itemQuery->where('service_id', $catalogItem->id);
            }

            $cartItem = $itemQuery->lockForUpdate()->first();
            $quantity = ($cartItem?->quantity ?? 0) + $data['quantity'];

            if ($quantity > 99) {
                throw ValidationException::withMessages([
                    'quantity' => 'A cart item cannot exceed 99 units.',
                ]);
            }

            if ($cartItem) {
                $cartItem->update(['quantity' => $quantity]);
            } else {
                $lockedCart->items()->create([
                    'product_id' => $itemType === 'product' ? $catalogItem->id : null,
                    'product_variant_id' => $itemType === 'product' ? $variant->id : null,
                    'service_id' => $itemType === 'service' ? $catalogItem->id : null,
                    'quantity' => $quantity,
                ]);
            }

            app(InventoryAvailabilityService::class)->assertCartAvailability(
                $lockedCart->items()->with('productVariant.supplies')->get(),
            );
        });

        return redirect()->route('cart')->with('status', Str::ucfirst($itemType).' added to your cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $ownedItem = $this->ownedCartItem($request, $cartItem);

        if (! $ownedItem->is_available) {
            return redirect()->route('cart')->withErrors(['cart' => 'This item is no longer available. Remove it from your cart.']);
        }

        DB::transaction(function () use ($ownedItem, $request): void {
            $cart = Cart::query()->whereKey($ownedItem->cart_id)->lockForUpdate()->firstOrFail();
            $cart->items()->whereKey($ownedItem->id)->lockForUpdate()->firstOrFail()
                ->update($request->validated());

            app(InventoryAvailabilityService::class)->assertCartAvailability(
                $cart->items()->with('productVariant.supplies')->get(),
            );
        });

        return redirect()->route('cart')->with('status', 'Cart quantity updated.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->ownedCartItem($request, $cartItem)->delete();

        return redirect()->route('cart')->with('status', 'Item removed from your cart.');
    }

    private function ownedCartItem(Request $request, CartItem $cartItem): CartItem
    {
        return $request->user()->cart()->firstOrFail()->items()->findOrFail($cartItem->id);
    }
}