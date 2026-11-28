<?php

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerifyEmailOtp;
use App\Notifications\VerifySmsOtp;
use App\Services\Verification\VerificationGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('registration sends both email and sms codes when a phone is supplied', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Verified Customer',
        'email' => 'both@example.com',
        'phone' => '639175550100',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('account.dashboard'));

    $user = User::where('email', 'both@example.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmailOtp::class);
    Notification::assertSentTo($user, VerifySmsOtp::class);

    expect($user->verificationCodes()->count())->toBe(2);
});

test('an email code verifies the account and cannot be reused', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->post(route('verification.send', 'email'))
        ->assertRedirect();

    $code = null;
    Notification::assertSentTo($user, VerifyEmailOtp::class, function (VerifyEmailOtp $notification) use (&$code): bool {
        $code = $notification->code;

        return true;
    });

    expect($code)->toHaveLength(6)
        ->and($code)->toMatch('/^\d{6}$/');

    $this->post(route('verification.verify', 'email'), ['code' => $code])
        ->assertRedirect(route('account.dashboard'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and($user->verificationCodes()->firstOrFail()->verified_at)->not->toBeNull();

    $this->post(route('verification.verify', 'email'), ['code' => $code])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('incorrect codes are rejected and attempts are limited', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)->post(route('verification.send', 'email'))->assertRedirect();

    $code = null;
    Notification::assertSentTo($user, VerifyEmailOtp::class, function (VerifyEmailOtp $notification) use (&$code): bool {
        $code = $notification->code;

        return true;
    });

    $wrongCode = $code === '000000' ? '111111' : '000000';

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('verification.verify', 'email'), ['code' => $wrongCode])
            ->assertSessionHasErrors('code');
    }

    $this->post(route('verification.verify', 'email'), ['code' => $code])
        ->assertSessionHasErrors('code');

    expect($user->verificationCodes()->firstOrFail()->attempts)->toBe(5)
        ->and($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a new code invalidates the previous one and resends are throttled', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)->post(route('verification.send', 'email'))->assertRedirect();

    $this->post(route('verification.send', 'email'))->assertSessionHasErrors('code');
    expect($user->verificationCodes()->count())->toBe(1);

    $this->travel(61)->seconds();
    $this->post(route('verification.send', 'email'))->assertRedirect();

    $codes = [];
    Notification::assertSentToTimes($user, VerifyEmailOtp::class, 2);
    Notification::assertSentTo($user, VerifyEmailOtp::class, function (VerifyEmailOtp $notification) use (&$codes): bool {
        $codes[] = $notification->code;

        return true;
    });

    expect($user->verificationCodes()->count())->toBe(2);

    $oldest = $user->verificationCodes()->oldest()->firstOrFail();
    expect($oldest->isExpired())->toBeTrue();

    $this->post(route('verification.verify', 'email'), ['code' => $codes[0]])
        ->assertSessionHasErrors('code');
    $this->post(route('verification.verify', 'email'), ['code' => $codes[1]])
        ->assertRedirect(route('account.dashboard'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('an sms code is delivered through the verification gateway and verifies the phone', function () {
    $user = User::factory()->create([
        'phone' => '639175550123',
        'email_verified_at' => now(),
        'phone_verified_at' => null,
    ]);

    $sent = [];
    $gateway = Mockery::mock(VerificationGateway::class);
    $gateway->shouldReceive('send')
        ->once()
        ->with('sms', (string) $user->phone, Mockery::on(function (string $code) use (&$sent): bool {
            $sent['code'] = $code;

            return true;
        }));
    $this->app->instance(VerificationGateway::class, $gateway);

    $this->actingAs($user)
        ->post(route('verification.send', 'sms'))
        ->assertRedirect();

    expect($sent['code'])->toMatch('/^\d{6}$/');

    $this->post(route('verification.verify', 'sms'), ['code' => $sent['code']])
        ->assertRedirect(route('account.dashboard'));

    expect($user->fresh()->hasVerifiedPhone())->toBeTrue()
        ->and($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('sms verification requires a phone number', function () {
    $user = User::factory()->create(['phone' => null]);

    $this->actingAs($user)
        ->post(route('verification.send', 'sms'))
        ->assertSessionHasErrors('phone');

    expect($user->verificationCodes()->where('channel', VerificationCode::CHANNEL_SMS)->count())->toBe(0);
});

test('changing an email clears verification and sends a fresh code', function () {
    Notification::fake();
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'phone' => '639175550199',
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
    ]);

    $this->actingAs($user)->patch(route('account.update'), [
        'name' => $user->name,
        'email' => 'new@example.com',
        'phone' => $user->phone,
    ])->assertRedirect(route('account.dashboard'));

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and($user->fresh()->hasVerifiedPhone())->toBeTrue();

    Notification::assertSentTo($user->fresh(), VerifyEmailOtp::class);
});

test('a code cannot verify a contact that changed after the code was issued', function () {
    Notification::fake();
    $user = User::factory()->create([
        'email' => 'first@example.com',
        'email_verified_at' => null,
    ]);

    $this->actingAs($user)->post(route('verification.send', 'email'))->assertRedirect();

    $code = null;
    Notification::assertSentTo($user, VerifyEmailOtp::class, function (VerifyEmailOtp $notification) use (&$code): bool {
        $code = $notification->code;

        return true;
    });

    $user->forceFill(['email' => 'second@example.com'])->save();

    $this->post(route('verification.verify', 'email'), ['code' => $code])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('an admin changing a managed phone clears its verification', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $customer = User::factory()->create([
        'phone' => '639175550101',
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
    ]);

    $this->actingAs($admin)->patch(route('admin.users.update', $customer), [
        'name' => $customer->name,
        'email' => $customer->email,
        'phone' => '639175550999',
        'role' => User::ROLE_CUSTOMER,
        'is_active' => true,
    ])->assertRedirect(route('admin.users.index'));

    expect($customer->fresh()->hasVerifiedPhone())->toBeFalse()
        ->and($customer->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('the http verification gateway posts the code with the configured api key', function () {
    config([
        'verification.api_url' => 'https://verify.example.com/send',
        'verification.api_key' => 'test-api-key',
        'verification.from_email' => 'no-reply@example.com',
    ]);
    Http::fake(['verify.example.com/*' => Http::response(['ok' => true])]);

    app(\App\Services\Verification\HttpVerificationGateway::class)
        ->send('email', 'customer@example.com', '123456');

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://verify.example.com/send'
            && $request['channel'] === 'email'
            && $request['to'] === 'customer@example.com'
            && $request['code'] === '123456'
            && $request->hasHeader('Authorization', 'Bearer test-api-key');
    });
});
