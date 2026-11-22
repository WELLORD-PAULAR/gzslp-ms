<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Order;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(ProcessPaymentRequest $request, Order $order): RedirectResponse
    {
        $order = $request->user()->orders()->findOrFail($order->id);

        try {
            $payment = app(PaymentService::class)->charge(
                $order,
                $request->user(),
                $request->validated('method'),
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        if ($payment->isFailed()) {
            return back()->withErrors([
                'payment' => $payment->failure_reason ?: 'The payment could not be processed.',
            ])->withInput();
        }

        return redirect()->route('orders.show', $order)
            ->with('status', 'Payment recorded. Reference: '.$payment->reference.'.');
    }
}
