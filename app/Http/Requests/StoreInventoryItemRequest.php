<?php

namespace App\Http\Requests;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-shop') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:inventory_items,sku'],
            'category' => ['required', Rule::in(InventoryItem::CATEGORIES)],
            'unit' => ['required', 'string', 'max:32'],
            'reorder_level' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999.999'],
            'is_active' => ['sometimes', 'boolean'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['integer', 'distinct', 'exists:suppliers,id'],
        ];
    }
}