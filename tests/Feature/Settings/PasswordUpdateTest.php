<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_update_page_is_displayed()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('user-password.edit', ['tenantSlug' => $tenant->slug]))
            ->assertOk();
    }

    public function test_password_can_be_updated()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $response = $this->actingAsTenant($user, $tenant, 'admin')->put(
            route('user-password.update', ['tenantSlug' => $tenant->slug]),
            [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]
        );

        $this->assertTrue(password_verify('new-password', $user->fresh()->password));
        $response->assertSessionHasNoErrors();
    }

    public function test_correct_password_must_be_provided_to_update_password()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $response = $this->actingAsTenant($user, $tenant, 'admin')->put(
            route('user-password.update', ['tenantSlug' => $tenant->slug]),
            [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]
        );

        $response->assertSessionHasErrors('current_password');
    }
}
