<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-active-account', fn (User $user): bool => $user->isActive());
        Gate::define('access-admin', fn (User $user): bool => $user->isActive() && $user->isAdmin());
        Gate::define('manage-shop', fn (User $user): bool => $user->isActive() && $user->isStaff());
        Gate::define('access-customer', fn (User $user): bool => $user->isActive()
            && ($user->role === User::ROLE_CUSTOMER || $user->isPreviewingStorefront()));
    }
}
