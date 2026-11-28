<?php

namespace App\Http\Requests\Concerns;

trait ExplainsPhoneErrors
{
    /** @return array<string, string> */
    protected function phoneMessages(): array
    {
        return [
            'phone.integer' => 'The phone number must contain digits only (no spaces, dashes, or symbols).',
            'phone.numeric' => 'The phone number must contain digits only.',
            'phone.digits_between' => 'The phone number must contain digits only (no spaces, dashes, or symbols).',
            'phone.max' => 'The phone number must contain no more than 15 digits.',
        ];
    }
}
