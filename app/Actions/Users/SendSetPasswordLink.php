<?php

namespace App\Actions\Users;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SendSetPasswordLink
{
    public function handle(User $user, ?Tenant $tenant = null): void
    {
        $throttle = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.throttle', 0);
        $key = sprintf('set-password:%s:%s', $tenant?->id ?? 'global', $user->id);

        if ($throttle > 0 && RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Um convite já foi enviado recentemente. Tente novamente em {$seconds} segundos.",
            ]);
        }

        $token = Password::broker()->createToken($user);
        $user->notify(new SetPasswordNotification($token, $tenant));

        if ($throttle > 0) {
            RateLimiter::hit($key, $throttle);
        }
    }
}
