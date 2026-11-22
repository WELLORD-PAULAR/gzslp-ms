<?php

namespace App\Channels;

use App\Services\Sms\SmsGateway;
use Illuminate\Notifications\Notification;

final class SmsChannel
{
    public function __construct(private readonly SmsGateway $gateway) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        $message = $notification->toSms($notifiable);
        $recipient = $notifiable->routeNotificationFor('sms', $notification) ?: $notifiable->phone;

        $this->gateway->send((string) $recipient, $message);
    }
}
