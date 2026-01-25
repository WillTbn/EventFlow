<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_update_page_is_displayed()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $this->actingAs($user)
            ->get(route('user-password.edit'))
            ->assertOk();
    }

    public function test_password_can_be_updated()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $response = $this->actingAs($user)->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $this->assertTrue(password_verify('new-password', $user->fresh()->password));
        $response->assertSessionHasNoErrors();
    }

    public function test_correct_password_must_be_provided_to_update_password()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $response = $this->actingAs($user)->put(route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    private function assignRole(User $user, string $role): void
    {
        Role::firstOrCreate(['name' => $role]);
        $user->assignRole($role);
    }
}
