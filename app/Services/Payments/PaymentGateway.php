<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Stable identifier stored on every payment record.
     */
    public function name(): string;

    /**
     * Charge the payment's amount and return the provider's outcome.
     */
    public function charge(Payment $payment): PaymentResult;
}
