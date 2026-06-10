<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (class_exists(\Laravel\Sanctum\SanctumServiceProvider::class)) {
            $this->app->register(\Laravel\Sanctum\SanctumServiceProvider::class);
        }

        $this->app->bind(
            \App\Repositories\Contracts\TenantRepositoryInterface::class,
            \App\Repositories\Eloquent\TenantRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TenantBusinessRepositoryInterface::class,
            \App\Repositories\Eloquent\TenantBusinessRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists(Sanctum::class)) {
            // 动态定位当前租户库里的 Token 表
            Sanctum::usePersonalAccessTokenModel(\App\Models\Tenant\SanctumToken::class);
        }

        // 定义权限拦截
        Gate::define('access-admin', function ($user) {
            return $user->role === 'admin';
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
