<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspacesController extends Controller
{
    /**
     * Display the workspaces for the authenticated user.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $tenants = $user->tenantMemberships()
            ->with('tenant')
            ->where('status', 'active')
            ->get()
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




