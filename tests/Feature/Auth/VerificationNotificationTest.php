<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;
use Tests\TestCase;

class VerificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_notification_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from(config('fortify.home'))
            ->post(route('verification.send'))
            ->assertRedirect(config('fortify.home'));

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    public function test_verification_notification_is_not_sent_if_verification_is_disabled(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification is not enabled.');
        }

        config(['fortify.features' => array_filter(
            config('fortify.features'),
            fn (string $feature) => $feature !== Features::emailVerification()
        )]);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post(route('verification.send'))
            ->assertRedirect(route('home'));
    }

    public function test_email_verification_link_is_invalid_when_not_signed(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification is not enabled.');
        }

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]));

        $response->assertForbidden();
    }
}

