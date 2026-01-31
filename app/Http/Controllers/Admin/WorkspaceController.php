<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Photos\StoreTenantLogo;
use App\Actions\Tenancy\UpdateTenantProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWorkspaceRequest;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * Show the workspace settings.
     */
    public function edit(): Response
    {
        $tenant = $this->tenantContext->get();

        return Inertia::render('admin/workspace/Edit', [
            'workspace' => [
                'name' => $tenant?->name,
                'slug' => $tenant?->slug,
                'logo_url' => $tenant?->logo_thumb_path
                    ? Storage::disk('public')->url($tenant->logo_thumb_path)
                    : null,
            ],
        ]);
    }

    /**
     * Update the workspace settings.
     */
    public function update(
        UpdateWorkspaceRequest $request,
        UpdateTenantProfile $updateTenantProfile,
        StoreTenantLogo $storeTenantLogo
    ): RedirectResponse {
        $tenant = $this->tenantContext->get();

        $before = [
            'name' => $tenant?->name,
            'slug' => $tenant?->slug,
            'logo_path' => $tenant?->logo_path,
            'logo_medium_path' => $tenant?->logo_medium_path,
            'logo_thumb_path' => $tenant?->logo_thumb_path,
        ];

        $validated = $request->validated();

        if ($tenant) {
            $updateTenantProfile->handle($tenant, $validated['name']);

            if ($request->hasFile('logo')) {
                $storeTenantLogo->handle($tenant, $request->file('logo'));
            }
        }

        $tenant?->refresh();

        $after = [
            'name' => $tenant?->name,
            'slug' => $tenant?->slug,
            'logo_path' => $tenant?->logo_path,
            'logo_medium_path' => $tenant?->logo_medium_path,
            'logo_thumb_path' => $tenant?->logo_thumb_path,
        ];

        logger()->info('Admin updated workspace settings.', [
            'tenant_id' => $tenant?->id,
            'user_id' => $request->user()?->id,
            'before' => $before,
            'after' => $after,
        ]);

        return to_route('admin.workspace.edit', [
            'tenantSlug' => $tenant?->slug,
        ]);
    }
}
