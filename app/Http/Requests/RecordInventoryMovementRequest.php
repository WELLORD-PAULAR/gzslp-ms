<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-shop') ?? false;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'type' => ['required', Rule::in(['received', 'used', 'stocktake'])],
            'quantity' => [
                'required',
                'numeric',
                'decimal:0,3',
                $type === 'stocktake' ? 'min:0' : 'gt:0',
                'max:999999999.999',
            ],
            'supplier_id' => ['nullable', 'required_if:type,received', 'integer', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}