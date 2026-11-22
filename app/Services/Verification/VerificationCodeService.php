<?php

namespace App\Services\Verification;

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerifyEmailOtp;
use App\Notifications\VerifySmsOtp;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

final class VerificationCodeService
{
    /**
     * Issue a fresh single-use code and deliver it through the given channel.
     * Pass $force when the destination itself just changed.
     */
    public function send(User $user, string $channel, bool $force = false): IssuedVerificationCode
    {
        $this->assertSupportedChannel($channel);

        $isEmail = $channel === VerificationCode::CHANNEL_EMAIL;
        $destination = $isEmail ? $user->email : $user->phone;

        if (blank($destination)) {
            throw ValidationException::withMessages([
                $isEmail ? 'email' : 'phone' => $isEmail
                    ? 'This account has no email address to verify.'
                    : 'Add a phone number to your account before requesting an SMS code.',
            ]);
        }

        $lastCode = VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->latest()
            ->first();

        if (! $force && $lastCode?->created_at?->gt(now()->subSeconds((int) config('verification.resend_seconds')))) {
            throw ValidationException::withMessages([
                'code' => 'Please wait '.config('verification.resend_seconds').' seconds before requesting another code.',
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $record = DB::transaction(function () use ($user, $channel, $destination, $code): VerificationCode {
            VerificationCode::query()
                ->where('user_id', $user->id)
                ->where('channel', $channel)
                ->whereNull('verified_at')
                ->update(['expires_at' => now()]);

            return VerificationCode::create([
                'user_id' => $user->id,
                'channel' => $channel,
                'destination' => $destination,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes((int) config('verification.code_ttl_minutes')),
            ]);
        });

        try {
            $user->notify($isEmail ? new VerifyEmailOtp($code) : new VerifySmsOtp($code));
        } catch (Throwable $exception) {
            // Do not leave an undelivered code blocking resends for a minute.
            $record->delete();
            report($exception);

            throw ValidationException::withMessages([
                'code' => 'We could not deliver the code. Please try again.',
            ]);
        }

        return new IssuedVerificationCode($record, $code);
    }

    /**
     * Check the newest live code and mark the matching contact as verified.
     */
    public function verify(User $user, string $channel, string $code): VerificationCode
    {
        $this->assertSupportedChannel($channel);

        $record = VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($record === null) {
            throw ValidationException::withMessages(['code' => 'There is no active verification code. Request a new one.']);
        }

        $currentDestination = $channel === VerificationCode::CHANNEL_EMAIL ? $user->email : $user->phone;

        if (! hash_equals((string) $record->destination, (string) $currentDestination)) {
            $record->update(['expires_at' => now()]);

            throw ValidationException::withMessages([
                'code' => 'That code was sent to a different contact. Request a new one.',
            ]);
        }

        if ($record->attempts >= (int) config('verification.max_attempts')) {
            throw ValidationException::withMessages(['code' => 'Too many incorrect attempts. Request a new code.']);
        }

        $record->increment('attempts');

        if (! Hash::check($code, $record->code_hash)) {
            throw ValidationException::withMessages(['code' => 'That code is incorrect. Check it and try again.']);
        }

        $record->update(['verified_at' => now()]);

        if ($channel === VerificationCode::CHANNEL_EMAIL) {
            if (! $user->hasVerifiedEmail()) {
                $user->forceFill(['email_verified_at' => now()])->save();

                event(new Verified($user));
            }
        } elseif (! $user->hasVerifiedPhone()) {
            $user->forceFill(['phone_verified_at' => now()])->save();
        }

        return $record;
    }

    private function assertSupportedChannel(string $channel): void
    {
        if (! in_array($channel, [VerificationCode::CHANNEL_EMAIL, VerificationCode::CHANNEL_SMS], true)) {
            throw ValidationException::withMessages(['channel' => 'Unsupported verification channel.']);
        }
    }
}
