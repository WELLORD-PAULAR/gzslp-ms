<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Str;

final class DummyPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'dummy';
    }

    public function charge(Payment $payment): PaymentResult
    {
        return PaymentResult::succeeded('DUM-'.Str::upper(Str::random(12)), [
            'gateway' => $this->name(),
            'payment_id' => $payment->id,
            'approved' => true,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
        ]);
    }
}
