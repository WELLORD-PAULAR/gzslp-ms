<?php

namespace App\Providers;

use App\Services\Payments\DummyPaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function (Application $app): PaymentGateway {
            return match (config('payments.gateway')) {
                'dummy' => $app->make(DummyPaymentGateway::class),
                default => throw new RuntimeException('Unsupported payment gateway: '.config('payments.gateway')),
            };
        });
    }
}
