<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'item_type' => ['required', Rule::in(['product', 'service'])],
            'item_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}