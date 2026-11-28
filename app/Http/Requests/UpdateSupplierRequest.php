<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ExplainsPhoneErrors;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
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
<<<<<<< HEAD
            'phone' => ['nullable', 'integer', 'digits_between:7,15'],
=======
            'phone' => ['nullable', 'digits_between:7,15'],
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->phoneMessages();
    }
}