<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('profile.edit', ['tenantSlug' => $tenant->slug]))
            ->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $response = $this->actingAsTenant($user, $tenant, 'admin')->patch(
            route('profile.update', ['tenantSlug' => $tenant->slug]),
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]
        );

        $this->assertEquals('Test User', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
        $response->assertSessionHasNoErrors();
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $response = $this->actingAsTenant($user, $tenant, 'admin')->patch(
            route('profile.update', ['tenantSlug' => $tenant->slug]),
            [
                'name' => 'Test User',
                'email' => $user->email,
            ]
        );

        $this->assertNotNull($user->fresh()->email_verified_at);
        $response->assertSessionHasNoErrors();
    }

    public function test_user_can_delete_their_account()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $response = $this->actingAsTenant($user, $tenant, 'admin')->delete(
            route('profile.destroy', ['tenantSlug' => $tenant->slug]),
            [
                'password' => 'password',
            ]
        );

        $this->assertNull($user->fresh());
        $response->assertRedirect('/');
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $response = $this->actingAsTenant($user, $tenant, 'admin')->delete(
            route('profile.destroy', ['tenantSlug' => $tenant->slug]),
            [
                'password' => 'wrong-password',
            ]
        );

        $this->assertNotNull($user->fresh());
        $response->assertSessionHasErrors('password');
    }
}
