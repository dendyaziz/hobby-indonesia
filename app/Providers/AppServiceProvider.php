<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use \Illuminate\Auth\Notifications\ResetPassword;
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
        Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);
        Gate::policy(\Spatie\Permission\Models\Permission::class, \App\Policies\PermissionPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return route('filament.admin.password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });
    }
}
