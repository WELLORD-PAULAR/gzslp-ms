<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {--name= : Administrator display name}
        {--email= : Administrator email address}
        {--password= : Administrator password (at least 8 characters)}';

    protected $description = 'Create or update an administrator account for local setup.';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Administrator name')));
        $email = strtolower(trim((string) ($this->option('email') ?: $this->ask('Administrator email'))));
        $password = (string) ($this->option('password') ?: $this->secret('Administrator password (at least 8 characters)'));

        $existing = User::where('email', $email)->first();

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($existing?->id)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = $existing ?? new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->role = User::ROLE_ADMIN;
        $user->is_active = true;
        $user->email_verified_at ??= now();
        $user->save();

        $this->info("Administrator ready: {$user->email}");

        return self::SUCCESS;
    }
}
