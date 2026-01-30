<?php

namespace App\Actions\Users;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateUserAndSendSetPasswordLink
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * Create or attach a user and send the set-password link when needed.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, string $role): User
    {
        $tenant = $this->tenantContext->get();
        $email = Str::lower($data['email']);

        $user = User::query()->where('email', $email)->first();
        $createdUser = false;

        if (! $user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Str::password(32),
                'email_verified_at' => null,
            ]);
            $createdUser = true;
        }

        $membership = TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('user_id', $user->id)
            ->first();

        $createdMembership = false;

        if (! $membership) {
            TenantUser::create([
                'tenant_id' => $tenant?->id,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'active',
            ]);
            $createdMembership = true;
        }

        if ($createdUser || $createdMembership) {
            $this->sendSetPasswordLink($user, $tenant);
        }

        return $user;
    }

    private function sendSetPasswordLink(User $user, ?Tenant $tenant = null): void
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
