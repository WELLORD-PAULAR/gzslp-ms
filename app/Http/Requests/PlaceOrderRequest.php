<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasImageValidationMessages;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    use HasImageValidationMessages;

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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages('artwork', 'JPG, PNG, or PDF', 10);
    }
}