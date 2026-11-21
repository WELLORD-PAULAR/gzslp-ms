<?php

namespace App\Services;

use App\Models\InventoryItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InventoryAvailabilityService
{
    public function assertCartAvailability(Collection $items): void
    {
        $requirements = [];

        foreach ($items as $item) {
            $variant = $item->productVariant;

            if (! $variant) {
                continue;
            }

            foreach ($variant->supplies as $supply) {
                $supplyId = $supply->id;
                $quantityPerUnitMilli = (int) round((float) $supply->pivot->quantity_per_unit * 1000);

                if ($quantityPerUnitMilli <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => $supply->name.' has an invalid product recipe and cannot be added to the cart.',
                    ]);
                }

                if (! isset($requirements[$supplyId])) {
                    $requirements[$supplyId] = [
                        'quantity_milli' => 0,
                        'name' => $supply->name,
                    ];
                }

                $requirements[$supplyId]['quantity_milli'] += $quantityPerUnitMilli * $item->quantity;
            }
        }

        if ($requirements === []) {
            return;
        }

        ksort($requirements);
        $supplies = InventoryItem::query()
            ->whereIn('id', array_keys($requirements))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($requirements as $supplyId => $requirement) {
            $supply = $supplies->get($supplyId);

            if (! $supply || ! $supply->is_active) {
                throw ValidationException::withMessages([
                    'quantity' => ($supply?->name ?? $requirement['name']).' is unavailable for production.',
                ]);
            }

            $availableMilli = (int) round((float) $supply->quantity_on_hand * 1000);

            if ($availableMilli < $requirement['quantity_milli']) {
                throw ValidationException::withMessages([
                    'quantity' => $supply->name.' does not have enough stock for the requested quantity.',
                ]);
            }
        }
    }
}
