<?php

namespace App\Services\Verification;

use App\Models\VerificationCode;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class HttpVerificationGateway implements VerificationGateway
{
    public function send(string $channel, string $destination, string $code): void
    {
        $url = (string) config('verification.api_url');

        if ($url === '') {
            throw new RuntimeException('Verification API URL is not configured.');
        }

        $apiKey = (string) config('verification.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('Verification API key is not configured.');
        }

        $this->request()
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'X-Api-Key' => $apiKey,
            ])
            ->post($url, [
                'channel' => $channel,
                'to' => $destination,
                'code' => $code,
                'from' => $channel === VerificationCode::CHANNEL_EMAIL
                    ? config('verification.from_email')
                    : config('verification.from_phone'),
            ])
            ->throw();
    }

    private function request(): PendingRequest
    {
        return Http::timeout((int) config('verification.timeout_seconds', 10))
            ->retry(2, 250);
    }
}
