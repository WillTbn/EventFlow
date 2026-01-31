<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $tenant = $this->createTenant();

        $response = $this->get(route('admin.dashboard', ['tenantSlug' => $tenant->slug]));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'starts_at' => now()->addDays(2),
        ]);

        $this->actingAsTenant($user, $tenant, 'admin');

        $response = $this->get(route('admin.dashboard', ['tenantSlug' => $tenant->slug]));
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('events')
                ->where('events.0.id', $event->id)
            );
    }
}
