<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a visitor can register and is signed in', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
<<<<<<< HEAD
        'phone' => 15550100,
=======
        'phone' => '15550100',
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => User::ROLE_ADMIN,
    ]);

    $response->assertRedirect(route('account.dashboard'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'customer@example.com',
<<<<<<< HEAD
        'phone' => 15550100,
=======
        'phone' => '15550100',
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
        'role' => User::ROLE_CUSTOMER,
    ]);
    expect(Hash::check('password123', User::first()->password))->toBeTrue();
});

test('registration validates unique email and confirmed password', function () {
    User::factory()->create(['email' => 'customer@example.com']);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123',
    ]);

    $response->assertSessionHasErrors(['email', 'password']);
    $this->assertGuest();
});

test('a user can log in and log out', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('account.dashboard'));
    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest();
});

test('customers cannot access the admin dashboard', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

<<<<<<< HEAD
    $this->get(route('home'))->assertOk()->assertSee('stand out.');
=======
    $this->get(route('home'))->assertOk()->assertSee('Your project, your way.');
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
    $this->get(route('account.dashboard'))->assertOk();
});

test('admins are redirected to and can access the admin dashboard', function () {
    $admin = User::factory()->create(['password' => 'password123']);
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password123',
    ])->assertRedirect(route('admin.dashboard'));

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('home'))->assertRedirect(route('admin.dashboard'));
});

test('the admin seeder creates an administrator from configured credentials', function () {
    config([
        'admin.name' => 'Shop Admin',
        'admin.email' => 'admin@example.com',
        'admin.password' => 'admin-password-123',
    ]);

    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', 'admin@example.com')->firstOrFail();

    expect($admin->isAdmin())->toBeTrue();
    expect(Hash::check('admin-password-123', $admin->password))->toBeTrue();
});

test('the create admin command bootstraps an active administrator', function () {
    $this->artisan('app:create-admin', [
        '--name' => 'Shop Admin',
        '--email' => 'admin@example.com',
        '--password' => 'admin-password-123',
    ])->assertSuccessful();

    $admin = User::where('email', 'admin@example.com')->firstOrFail();

    expect($admin->isAdmin())->toBeTrue();
    expect($admin->isActive())->toBeTrue();
    expect($admin->hasVerifiedEmail())->toBeTrue();
    expect(Hash::check('admin-password-123', $admin->password))->toBeTrue();
});

test('repeated failed login attempts are rate limited', function () {
    foreach (range(1, 6) as $attempt) {
        $response = $this->post(route('login.store'), [
            'email' => 'unknown@example.com',
            'password' => 'incorrect-password',
        ]);

        $response->assertSessionHasErrors('email');
    }
});