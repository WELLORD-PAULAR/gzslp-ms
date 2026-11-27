<?php

namespace App\Channels;

use App\Services\Verification\VerificationGateway;
use Illuminate\Notifications\Notification;

final class VerificationChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toVerification($notifiable);

        app(VerificationGateway::class)->send(
            $message->channel,
            $message->destination,
            $message->code,
        );
    }
}
