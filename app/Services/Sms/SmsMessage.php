<?php

namespace App\Services\Sms;

final readonly class SmsMessage
{
    public function __construct(public string $content) {}

    public static function make(string $content): self
    {
        return new self($content);
    }
}
