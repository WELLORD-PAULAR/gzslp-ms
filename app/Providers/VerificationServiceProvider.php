<?php

namespace App\Providers;

use App\Channels\VerificationChannel;
use App\Services\Verification\HttpVerificationGateway;
use App\Services\Verification\LogVerificationGateway;
use App\Services\Verification\VerificationGateway;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class VerificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Notification::extend('verification', fn (Application $app) => $app->make(VerificationChannel::class));
    }

    public function register(): void
    {
        $this->app->bind(VerificationGateway::class, function (Application $app): VerificationGateway {
            return match (config('verification.gateway')) {
                'http' => $app->make(HttpVerificationGateway::class),
                'log' => $app->make(LogVerificationGateway::class),
                default => throw new RuntimeException('Unsupported verification gateway: '.config('verification.gateway')),
            };
        });
    }
}
