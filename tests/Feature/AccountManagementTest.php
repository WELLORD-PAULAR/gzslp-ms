<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('a customer can update personal details and change password', function () {
    $customer = User::factory()->create([
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($customer)
        ->get(route('account.edit'))
        ->assertOk();

    $this->patch(route('account.update'), [
        'name' => 'Updated Customer',
        'email' => 'new@example.com',
        'phone' => '+63 912 345 6789',
    ])->assertRedirect(route('account.dashboard'));

    expect($customer->fresh()->name)->toBe('Updated Customer')
        ->and($customer->fresh()->email)->toBe('new@example.com')
        ->and($customer->fresh()->phone)->toBe('+63 912 345 6789')
        ->and($customer->fresh()->email_verified_at)->toBeNull();

    $this->patch(route('account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect(route('account.dashboard'));

    expect(Hash::check('new-password-123', $customer->fresh()->password))->toBeTrue();
});

test('a customer cannot change their password without the current password', function () {
    $customer = User::factory()->create();
    $oldPasswordHash = $customer->password;

    $this->actingAs($customer)
        ->from(route('account.edit'))
        ->patch(route('account.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertSessionHasErrors('current_password');

    expect($customer->fresh()->password)->toBe($oldPasswordHash);
});

test('an admin can create and filter accounts and deactivate an account without deleting its orders', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $customer = User::factory()->create();
    $customer->orders()->create(['status' => Order::STATUS_PENDING, 'subtotal' => '10.00']);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => $customer->email, 'role' => User::ROLE_CUSTOMER]))
        ->assertOk()
        ->assertSee($customer->email)
        ->assertDontSee($admin->email);

    $this->get(route('admin.users.create'))->assertOk();
    $this->post(route('admin.users.store'), [
        'name' => 'Print Employee',
        'email' => 'employee@example.com',
        'phone' => '5550101',
        'role' => User::ROLE_EMPLOYEE,
        'is_active' => '1',
        'password' => 'employee-password',
        'password_confirmation' => 'employee-password',
    ])->assertRedirect(route('admin.users.index'));

    $employee = User::where('email', 'employee@example.com')->firstOrFail();
    expect($employee->role)->toBe(User::ROLE_EMPLOYEE)
        ->and(Hash::check('employee-password', $employee->password))->toBeTrue();

    $this->put(route('admin.users.update', $customer), [
        'name' => $customer->name,
        'email' => $customer->email,
        'phone' => $customer->phone,
        'role' => User::ROLE_CUSTOMER,
        'is_active' => '0',
        'password' => '',
        'password_confirmation' => '',
    ])->assertRedirect(route('admin.users.index'));

    expect($customer->fresh()->is_active)->toBeFalse()
        ->and($customer->orders()->count())->toBe(1);
});

test('the last active administrator cannot be demoted or deactivated', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->actingAs($admin)
        ->from(route('admin.users.edit', $admin))
        ->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => '',
            'role' => User::ROLE_EMPLOYEE,
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->isAdmin())->toBeTrue()
        ->and($admin->fresh()->is_active)->toBeTrue();
});

test('an admin can delete an account without history but cannot delete the current or historical account', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $customer = User::factory()->create();
    $customerWithOrder = User::factory()->create();
    $customerWithOrder->orders()->create(['status' => Order::STATUS_PENDING, 'subtotal' => '10.00']);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $customer))
        ->assertRedirect(route('admin.users.index'));
    $this->assertDatabaseMissing('users', ['id' => $customer->id]);

    $this->delete(route('admin.users.destroy', $customerWithOrder))
        ->assertRedirect()
        ->assertSessionHasErrors('account');
    $this->assertDatabaseHas('users', ['id' => $customerWithOrder->id]);

    $this->delete(route('admin.users.destroy', $admin))
        ->assertRedirect()
        ->assertSessionHasErrors('account');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('employees can manage shop operations but cannot access admin-only reports or accounts', function () {
    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();

    $this->actingAs($employee)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->get(route('admin.products.index'))->assertOk();
    $this->get(route('admin.orders.index'))->assertOk();
    $this->get(route('admin.reports.index'))->assertForbidden();
    $this->get(route('admin.users.index'))->assertForbidden();
});

test('inactive accounts cannot sign in', function () {
    $user = User::factory()->create(['email' => 'inactive@example.com']);
    $user->forceFill(['is_active' => false])->save();

    $this->post(route('login.store'), [
        'email' => 'inactive@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('registration sends an email verification notification and the signed link verifies the account', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Verify Customer',
        'email' => 'verify@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('account.dashboard'));

    $user = User::where('email', 'verify@example.com')->firstOrFail();
    Notification::assertSentTo($user, VerifyEmail::class);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
    );

    $this->get($verificationUrl)->assertRedirect(route('account.dashboard'));
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('a customer can request and use a password reset token', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHas('status');

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
        $token = $notification->token;

        return true;
    });

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'reset-password-123',
        'password_confirmation' => 'reset-password-123',
    ])->assertRedirect(route('login'));

    expect(Hash::check('reset-password-123', $user->fresh()->password))->toBeTrue();
});