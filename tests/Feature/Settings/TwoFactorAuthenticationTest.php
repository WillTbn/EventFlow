<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_settings_page_can_be_rendered()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $this->actingAsTenant($user, $tenant, 'admin')
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('two-factor.show', ['tenantSlug' => $tenant->slug]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/TwoFactor')
                ->where('twoFactorEnabled', false)
            );
    }

    public function test_two_factor_settings_page_requires_password_confirmation_when_enabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $response = $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('two-factor.show', ['tenantSlug' => $tenant->slug]));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_two_factor_settings_page_does_not_requires_password_confirmation_when_disabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]);

        $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('two-factor.show', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/TwoFactor')
            );
    }

    public function test_two_factor_settings_page_returns_forbidden_response_when_two_factor_is_disabled()
    {
        $features = array_filter(
            config('fortify.features', []),
            fn (string $feature) => $feature !== Features::twoFactorAuthentication()
        );

        config(['fortify.features' => array_values($features)]);

        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $response = $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('two-factor.show', ['tenantSlug' => $tenant->slug]));

        $response->assertForbidden();
    }
}
