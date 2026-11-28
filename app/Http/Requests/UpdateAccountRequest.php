<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ExplainsPhoneErrors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    use ExplainsPhoneErrors;

    public function authorize(): bool
    {
        return $this->user()?->can('access-active-account') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
<<<<<<< HEAD
            'phone' => ['nullable', 'integer', 'digits_between:7,15'],
=======
            'phone' => ['nullable', 'digits_between:7,15'],
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->phoneMessages();
    }
}