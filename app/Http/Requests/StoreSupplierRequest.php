<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ExplainsPhoneErrors;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    use ExplainsPhoneErrors;

    public function authorize(): bool
    {
        return $this->user()?->can('manage-shop') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'digits_between:7,15'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->phoneMessages();
    }
}
