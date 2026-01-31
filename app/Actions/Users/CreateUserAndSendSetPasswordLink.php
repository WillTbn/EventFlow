<?php

namespace App\Actions\Users;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Str;

class CreateUserAndSendSetPasswordLink
{
    public function __construct(
        private TenantContext $tenantContext,
        private SendSetPasswordLink $sendSetPasswordLink
    )
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
            $this->sendSetPasswordLink->handle($user, $tenant);
        }

        return $user;
    }
}
