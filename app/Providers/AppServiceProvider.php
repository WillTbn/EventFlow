<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, fn () => new TenantContext());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRouteBindings();
    }

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
            : null
        );
    }

    protected function configureRouteBindings(): void
    {
        Route::bind('event', function (string $value): Event {
            $tenantContext = app(TenantContext::class);
            $tenant = $tenantContext->get();

            if (! $tenant) {
                $tenantSlug = request()->route('tenantSlug') ?? request()->route('tenant');
                $tenant = $tenantSlug
                    ? Tenant::query()->where('slug', $tenantSlug)->first()
                    : null;

                if ($tenant) {
                    $tenantContext->set($tenant);
                }
            }

            if (! $tenant) {
                abort(404);
            }

            return Event::query()
                ->where('hash_id', $value)
                ->firstOrFail();
        });

        Route::bind('user', function (string $value): User {
            $tenantId = app(TenantContext::class)->id();

            if (! $tenantId) {
                $tenantSlug = request()->route('tenantSlug') ?? request()->route('tenant');
                $tenantId = $tenantSlug
                    ? Tenant::query()->where('slug', $tenantSlug)->value('id')
                    : null;
            }

            if (! $tenantId) {
                abort(404);
            }

            return User::query()
                ->where('hash_id', $value)
                ->whereHas('tenantMemberships', function ($query) use ($tenantId): void {
                    $query
                        ->where('tenant_id', $tenantId)
                        ->where('status', 'active');
                })
                ->firstOrFail();
        });
    }
}
