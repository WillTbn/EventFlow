<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class WorkspacesController extends Controller
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * Display the workspaces for the authenticated user.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        $memberships = $user->tenantMemberships()
            ->with('tenant')
            ->where('status', 'active')
            ->get()
            ->filter(fn ($membership) => $membership->tenant);

        $activeTenants = $memberships
            ->filter(fn ($membership) => $membership->tenant?->status === 'active');

        if ($activeTenants->count() === 1) {
            /** @var Tenant $tenant */
            $tenant = $activeTenants->first()->tenant;

            $this->tenantContext->set($tenant);

            return redirect()->to('/t/'.$tenant->slug.'/admin');
        }

        $tenants = $memberships
            ->map(fn ($membership) => [
                'id' => $membership->tenant?->id,
                'name' => $membership->tenant?->name,
                'slug' => $membership->tenant?->slug,
                'plan' => $membership->tenant?->plan,
                'status' => $membership->tenant?->status,
                'role' => $membership->role,
            ])
            ->filter(fn ($tenant) => $tenant['id']);

        return Inertia::render('central/Workspaces', [
            'tenants' => $tenants->values()->all(),
        ]);
    }
}




