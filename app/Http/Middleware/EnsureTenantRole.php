<?php

namespace App\Http\Middleware;

use App\Models\TenantUser;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRole
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $tenant = $this->tenantContext->get();

        if (! $tenant) {
            abort(404);
        }

        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => preg_split('/[|,]/', $role))
            ->filter()
            ->values()
            ->all();

        $membership = TenantUser::query()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $membership || ! in_array($membership->role, $allowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
