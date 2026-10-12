<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class InventoryConsumptionService
{
    public function consumeForOrder(Order $order, User $actor): void
    {
        $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

        if ($order->inventory_consumed_at !== null) {
            return;
        }

        $order->loadMissing('items.productVariant.supplies');
        $requirements = [];

        foreach ($order->items as $orderItem) {
            if ($orderItem->product_id === null) {
                continue;
            }

            $variant = $orderItem->productVariant;

            if (! $variant || ! $variant->is_active) {
                throw ValidationException::withMessages([
                    'inventory' => 'An ordered product option is no longer available.',
                ]);
            }

            if ($variant->supplies->isEmpty()) {
                throw ValidationException::withMessages([
                    'inventory' => 'Add a raw-supply recipe to '.$orderItem->item_name.' before starting this order.',
                ]);
            }

            foreach ($variant->supplies as $supply) {
                if (! $supply->is_active) {
                    throw ValidationException::withMessages([
                        'inventory' => $supply->name.' is inactive and cannot be used for production.',
                    ]);
                }

                $perUnitMilli = (int) round((float) $supply->pivot->quantity_per_unit * 1000);
                $requiredMilli = $perUnitMilli * $orderItem->quantity;
                $supplyId = $supply->id;

                if (! isset($requirements[$supplyId])) {
                    $requirements[$supplyId] = [
                        'quantity_milli' => 0,
                        'variant_names' => [],
                    ];
                }

                $requirements[$supplyId]['quantity_milli'] += $requiredMilli;
                $requirements[$supplyId]['variant_names'][] = $orderItem->variant_name ?? $orderItem->item_name;
            }
        }

        ksort($requirements);
        $supplyItems = InventoryItem::query()
            ->whereIn('id', array_keys($requirements))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($requirements as $supplyId => $requirement) {
            $supply = $supplyItems->get($supplyId);

            if (! $supply || (int) round((float) $supply->quantity_on_hand * 1000) < $requirement['quantity_milli']) {
                $supplyName = $supply?->name ?? 'A required supply';
                $available = $supply ? number_format((float) $supply->quantity_on_hand, 3) : '0.000';
                $needed = number_format($requirement['quantity_milli'] / 1000, 3);

                throw ValidationException::withMessages([
                    'inventory' => $supplyName.' has '.$available.' available; '.$needed.' is needed to start this order.',
                ]);
            }
        }

        foreach ($requirements as $supplyId => $requirement) {
            $supply = $supplyItems->get($supplyId);
            $beforeMilli = (int) round((float) $supply->quantity_on_hand * 1000);
            $afterMilli = $beforeMilli - $requirement['quantity_milli'];
            $supply->quantity_on_hand = number_format($afterMilli / 1000, 3, '.', '');
            $supply->save();

            InventoryMovement::create([
                'inventory_item_id' => $supply->id,
                'user_id' => $actor->id,
                'order_id' => $order->id,
                'type' => InventoryMovement::TYPE_USED,
                'quantity_change' => number_format(-$requirement['quantity_milli'] / 1000, 3, '.', ''),
                'quantity_before' => number_format($beforeMilli / 1000, 3, '.', ''),
                'quantity_after' => number_format($afterMilli / 1000, 3, '.', ''),
                'notes' => 'Production started for order #'.$order->id.' ('.implode(', ', array_unique($requirement['variant_names'])).')',
                'created_at' => now(),
            ]);
        }

        $order->inventory_consumed_at = now();
        $order->save();
    }
}
