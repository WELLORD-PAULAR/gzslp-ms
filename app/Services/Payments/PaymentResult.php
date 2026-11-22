<?php

namespace App\Services\Payments;

final readonly class PaymentResult
{
    public function __construct(
        public bool $successful,
        public ?string $reference = null,
        public ?string $message = null,
        public array $response = [],
    ) {}

    public static function succeeded(string $reference, array $response = []): self
    {
        return new self(true, $reference, null, $response);
    }

    public static function failed(string $message, array $response = []): self
    {
        return new self(false, null, $message, $response);
    }
}
