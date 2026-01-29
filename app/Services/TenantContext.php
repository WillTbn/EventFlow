<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Contracts\Session\Session;

class TenantContext
{
    public const SESSION_KEY = 'current_tenant_id';

    /**
     * Set the current tenant in session and container.
     */
    public function set(?Tenant $tenant): void
    {
        if ($tenant) {
            $this->session()?->put(self::SESSION_KEY, $tenant->id);
            app()->instance('currentTenant', $tenant);
            return;
        }

        $this->session()?->forget(self::SESSION_KEY);
        app()->forgetInstance('currentTenant');
    }

    /**
     * Get the current tenant.
     */
    public function get(): ?Tenant
    {
        if (app()->bound('currentTenant')) {
            return app('currentTenant');
        }

        $tenantId = $this->session()?->get(self::SESSION_KEY);

        if (! $tenantId) {
            return null;
        }

        $tenant = Tenant::find($tenantId);

        if ($tenant) {
            app()->instance('currentTenant', $tenant);
        }

        return $tenant;
    }

    /**
     * Get the current tenant id.
     */
    public function id(): ?int
    {
        return $this->get()?->id;
    }

    /**
     * Clear the current tenant context.
     */
    public function clear(): void
    {
        $this->set(null);
    }

    protected function session(): ?Session
    {
        return app()->bound('session.store') ? app('session.store') : null;
    }
}
