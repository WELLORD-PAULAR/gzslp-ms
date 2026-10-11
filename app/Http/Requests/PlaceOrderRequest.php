<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_note' => ['nullable', 'string', 'max:5000'],
            'artwork' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];
    }
}