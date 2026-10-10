<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'starting_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}