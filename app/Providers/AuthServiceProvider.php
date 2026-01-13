<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define(
            'admin',
            fn($user) =>
            $user->role->name === 'admin'
        );

        Gate::define(
            'moderator',
            fn($user) => ($user->role->name === 'moderator' || $user->role->name === "admin")
        );
    }
}
