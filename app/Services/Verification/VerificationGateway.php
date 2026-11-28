<?php

namespace App\Services\Verification;

interface VerificationGateway
{
    /**
     * Deliver a one-time code to an email address or phone number.
     *
     * @param  string  $channel  'email' or 'sms'
     * @param  string  $destination  Email address or phone number
     * @param  string  $code  Six-digit one-time code
     */
    public function send(string $channel, string $destination, string $code): void;
}
