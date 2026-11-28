<?php

namespace App\Services\Verification;

final readonly class VerificationMessage
{
    public function __construct(
        public string $channel,
        public string $destination,
        public string $code,
    ) {}
}
