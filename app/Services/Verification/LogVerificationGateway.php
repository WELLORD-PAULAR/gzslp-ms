<?php

namespace App\Services\Verification;

use Illuminate\Support\Facades\Log;

final class LogVerificationGateway implements VerificationGateway
{
    public function send(string $channel, string $destination, string $code): void
    {
        Log::channel((string) config('verification.log_channel', 'single'))
            ->info("Verification [{$channel}] to {$destination}: {$code}");
    }
}
