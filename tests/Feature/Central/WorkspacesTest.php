<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkspacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspaces_lists_only_user_tenants(): void
    {
        $user = User::factory()->create();

        $tenantOne = Tenant::factory()->create(['name' => 'Workspace One']);
        $tenantTwo = Tenant::factory()->create(['name' => 'Workspace Two']);
        $tenantOther = Tenant::factory()->create(['name' => 'Other Workspace']);

        $this->attachUserToTenant($user, $tenantOne, 'admin');
        $this->attachUserToTenant($user, $tenantTwo, 'member');

        $otherUser = User::factory()->create();
        $this->attachUserToTenant($otherUser, $tenantOther, 'admin');

        $this->actingAs($user)
            ->get(route('workspaces'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('central/Workspaces')
                ->has('tenants', 2)
                ->where('tenants', function ($tenants) {
                    $names = collect($tenants)->pluck('name')->sort()->values()->all();
                    return $names === ['Workspace One', 'Workspace Two'];
                })
            );
    }
}

