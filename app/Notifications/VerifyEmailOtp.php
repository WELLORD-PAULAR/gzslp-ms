<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your Graphic Zone account')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Use the code below to verify your email address.')
            ->line('**'.$this->code.'**')
            ->line('This code expires in '.config('verification.code_ttl_minutes').' minutes and can only be used once.')
            ->line('If you did not create this account, you can ignore this message.');
    }
}
