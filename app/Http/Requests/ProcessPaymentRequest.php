<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-customer') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(array_keys(config('payments.methods')))],
        ];
    }
}
