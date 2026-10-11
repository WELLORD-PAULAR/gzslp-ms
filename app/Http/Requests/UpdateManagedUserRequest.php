<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user')->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_EMPLOYEE, User::ROLE_ADMIN])],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}