<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test User', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
        $response->assertSessionHasNoErrors();
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $response->assertSessionHasNoErrors();
    }

    public function test_user_can_delete_their_account()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

        $this->assertNull($user->fresh());
        $response->assertRedirect('/');
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

        $this->assertNotNull($user->fresh());
        $response->assertSessionHasErrors('password');
    }

    private function assignRole(User $user, string $role): void
    {
        Role::firstOrCreate(['name' => $role]);
        $user->assignRole($role);
    }
}
