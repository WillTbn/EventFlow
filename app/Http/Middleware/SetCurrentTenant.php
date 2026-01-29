<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tenantSlug') ?? $request->route('tenant');

        if (! $slug) {
            abort(404);
        }

        $tenant = Tenant::query()->where('slug', $slug)->first();

        if (! $tenant) {
            abort(404);
        }

        if ($tenant->status !== 'active') {
            abort(403);
        }

        $user = $request->user();

        if ($user) {
            $membership = TenantUser::query()
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (! $membership) {
                abort(403);
            }

            $request->attributes->set('tenantUser', $membership);
        }

        $this->tenantContext->set($tenant);

        return $next($request);
    }
}
