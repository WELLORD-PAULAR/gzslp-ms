<?php

namespace App\Providers;

use App\Channels\SmsChannel;
use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class SmsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Notification::extend('sms', fn (Application $app) => $app->make(SmsChannel::class));
    }

    public function register(): void
    {
        $this->app->bind(SmsGateway::class, function (Application $app): SmsGateway {
            return match (config('sms.gateway')) {
                'log' => $app->make(LogSmsGateway::class),
                default => throw new RuntimeException('Unsupported SMS gateway: '.config('sms.gateway')),
            };
        });
    }
}
