<?php

namespace App\Notifications;

use App\Channels\VerificationChannel;
use App\Models\VerificationCode;
use App\Services\Verification\VerificationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VerifyEmailOtp extends Notification
{
    use Queueable;

    public function __construct(public readonly string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [VerificationChannel::class];
    }

    public function toVerification(object $notifiable): VerificationMessage
    {
        return new VerificationMessage(
            VerificationCode::CHANNEL_EMAIL,
            (string) $notifiable->email,
            $this->code,
        );
    }
}
