<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('admin.name');
        $email = config('admin.email');
        $password = config('admin.password');

        if (! $name || ! $email || ! $password) {
            $this->command?->warn('Admin credentials are not configured. Set ADMIN_* in .env or run php artisan app:create-admin.');

            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = $password;
        $user->role = User::ROLE_ADMIN;
        $user->is_active = true;
        $user->email_verified_at ??= now();
        $user->save();
    }
}