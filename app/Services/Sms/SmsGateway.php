<?php

namespace App\Services\Sms;

interface SmsGateway
{
    public function send(string $recipient, SmsMessage $message): void;
}
