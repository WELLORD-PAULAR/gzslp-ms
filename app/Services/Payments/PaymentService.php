<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class PaymentService
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    /**
     * Charge the outstanding balance on an order and persist the outcome.
     *
     * A failed charge is kept as a failed payment record so support staff can
     * see the attempt; invalid order states throw before any record is made.
     */
    public function charge(Order $order, User $user, string $method = 'card'): Payment
    {
        return DB::transaction(function () use ($order, $user, $method): Payment {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->user_id !== $user->id) {
                throw ValidationException::withMessages(['payment' => 'You cannot pay for this order.']);
            }

            if ($lockedOrder->status === Order::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['payment' => 'A cancelled order cannot be paid.']);
            }

            if (! $lockedOrder->hasFinalTotal()) {
                throw ValidationException::withMessages(['payment' => 'This order is not ready for payment yet. A quoted order must be accepted first.']);
            }

            if (! $lockedOrder->needsPayment()) {
                throw ValidationException::withMessages(['payment' => 'This order has already been paid in full.']);
            }

            $payment = $user->payments()->create([
                'order_id' => $lockedOrder->id,
                'gateway' => $this->gateway->name(),
                'method' => $method,
                'amount' => number_format($lockedOrder->amountDueCents() / 100, 2, '.', ''),
                'currency' => config('payments.currency'),
                'status' => Payment::STATUS_PENDING,
            ]);

            try {
                $result = $this->gateway->charge($payment);
            } catch (Throwable $exception) {
                report($exception);
                $result = PaymentResult::failed('The payment gateway could not be reached.', [
                    'error' => $exception->getMessage(),
                ]);
            }

            $payment->forceFill([
                'status' => $result->successful ? Payment::STATUS_SUCCEEDED : Payment::STATUS_FAILED,
                'reference' => $result->reference,
                'failure_reason' => $result->successful ? null : ($result->message ?: 'The payment was declined.'),
                'gateway_response' => $result->response,
            ])->save();

            return $payment;
        });
    }
}
