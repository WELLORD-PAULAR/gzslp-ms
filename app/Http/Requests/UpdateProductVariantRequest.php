<?php

namespace App\Http\Requests;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-shop') ?? false;
    }

    public function rules(): array
    {
        $variantId = $this->route('productVariant')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($variantId)],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'options' => ['nullable', 'array', 'max:8'],
            'options.*' => ['nullable', 'string', 'max:80'],
            'supplies' => ['nullable', 'array'],
            'supplies.*' => ['nullable', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
            'is_active' => ['required', 'boolean'],
            'lock_version' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $supplies = $this->input('supplies', []);

            if (! is_array($supplies)) {
                return;
            }

            foreach (array_keys($supplies) as $inventoryItemId) {
                if (! ctype_digit((string) $inventoryItemId) || ! InventoryItem::whereKey($inventoryItemId)->where('is_active', true)->exists()) {
                    $validator->errors()->add('supplies', 'Choose active supply items for the variant recipe.');
                    break;
                }
            }
        });
    }
}