<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'paper_size' => ['nullable', 'string', 'max:80'],
            'paper_weight' => ['nullable', 'string', 'max:80'],
            'paper_finish' => ['nullable', 'string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
