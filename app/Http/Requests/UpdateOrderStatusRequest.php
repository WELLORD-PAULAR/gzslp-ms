<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-shop') ?? false;
    }

    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');

        return [
            'status' => ['required', Rule::in($order->allowedAdminStatuses())],
            'quoted_total' => [
                Rule::requiredIf($this->input('status') === Order::STATUS_QUOTE_SENT),
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:99999999.99',
            ],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}