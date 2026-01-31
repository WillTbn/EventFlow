<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = app(TenantContext::class)->get();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'tenant_role' => $request->user()?->tenantRole($tenant),
            ],
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'logo_url' => $tenant->logo_medium_path
                    ? Storage::disk('public')->url($tenant->logo_medium_path)
                    : null,
                'logo_thumb_url' => $tenant->logo_thumb_path
                    ? Storage::disk('public')->url($tenant->logo_thumb_path)
                    : null,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
                'trial_ends_at' => $tenant->trial_ends_at,
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
