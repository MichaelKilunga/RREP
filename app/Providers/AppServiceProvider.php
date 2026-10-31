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
        // Every RBAC permission name (view_leads, manage_projects, ...) is a Gate
        // ability, so `can:` route middleware and @can in Blade read the matrix
        // managed in Settings -> Roles & Permissions. Returning null (never false)
        // lets any explicitly defined gates or policies still decide.
        Gate::before(function (User $user, string $ability) {
            return $user->hasPermission($ability) ? true : null;
        });
    }
}
