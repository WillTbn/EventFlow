<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a tenant for tests.
     */
    protected function createTenant(array $attributes = []): Tenant
    {
        return Tenant::factory()->create($attributes);
    }

    /**
     * Attach a user to a tenant with a role.
     */
    protected function attachUserToTenant(
        User $user,
        Tenant $tenant,
        string $role = 'member',
        string $status = 'active'
    ): TenantUser {
        return TenantUser::updateOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ], [
            'role' => $role,
            'status' => $status,
        ]);
    }

    /**
     * Act as a user within a tenant context.
     */
    protected function actingAsTenant(User $user, Tenant $tenant, string $role = 'member'): self
    {
        $this->attachUserToTenant($user, $tenant, $role);
        app(TenantContext::class)->set($tenant);

        return $this->actingAs($user)->withSession([
            TenantContext::SESSION_KEY => $tenant->id,
        ]);
    }

    /**
     * Set the tenant context for the current test.
     */
    protected function withTenantContext(Tenant $tenant): self
    {
        app(TenantContext::class)->set($tenant);

        return $this->withSession([
            TenantContext::SESSION_KEY => $tenant->id,
        ]);
    }
}

