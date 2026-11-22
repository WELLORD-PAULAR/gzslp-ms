<?php

namespace App\Services\Verification;

use App\Models\VerificationCode;

final readonly class IssuedVerificationCode
{
    public function __construct(
        public VerificationCode $record,
        public string $code,
    ) {}
}
