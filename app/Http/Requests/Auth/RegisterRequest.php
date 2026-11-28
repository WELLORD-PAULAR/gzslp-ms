<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\ExplainsPhoneErrors;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    use ExplainsPhoneErrors;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
<<<<<<< HEAD
            'phone' => ['nullable', 'integer', 'digits_between:7,15'],
=======
            'phone' => ['nullable', 'digits_between:7,15'],
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->phoneMessages();
    }
}