<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

final class LogSmsGateway implements SmsGateway
{
    public function send(string $recipient, SmsMessage $message): void
    {
        Log::channel(config('sms.log_channel', 'single'))
            ->info('SMS to '.$recipient.': '.$message->content);
    }
}
