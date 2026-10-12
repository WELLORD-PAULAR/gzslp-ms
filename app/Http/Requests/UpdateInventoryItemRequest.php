<?php

namespace App\Http\Requests;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-shop') ?? false;
    }

    public function rules(): array
    {
        $itemId = $this->route('inventoryItem')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('inventory_items', 'sku')->ignore($itemId)],
            'category' => ['required', Rule::in(InventoryItem::CATEGORIES)],
            'unit' => ['required', 'string', 'max:32'],
            'reorder_level' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999.999'],
            'is_active' => ['required', 'boolean'],
            'lock_version' => ['required', 'integer', 'min:0'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['integer', 'distinct', 'exists:suppliers,id'],
        ];
    }
}