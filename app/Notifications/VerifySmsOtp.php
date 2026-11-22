<?php

namespace App\Notifications;

use App\Services\Sms\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VerifySmsOtp extends Notification
{
    use Queueable;

    public function __construct(public readonly string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['sms'];
    }

    public function toSms(object $notifiable): SmsMessage
    {
        return SmsMessage::make(
            'Graphic Zone verification code: '.$this->code
            .'. It expires in '.config('verification.code_ttl_minutes').' minutes. Do not share this code.',
        );
    }
}
