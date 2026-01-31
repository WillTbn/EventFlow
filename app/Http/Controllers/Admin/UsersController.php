<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Actions\Users\CreateUserAndSendSetPasswordLink;
use App\Actions\Users\SendSetPasswordLink;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private TenantContext $tenantContext)
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        $tenant = $this->tenantContext->get();
        $currentRole = $request->user()?->tenantRole($tenant);
        // $userLogged = Auth()->user();
        $query = TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('status', 'active')
            ->whereNot('id', $request->user()->id)
            ->with('user')
            ->orderBy(
                User::select('name')->whereColumn('users.id', 'tenant_users.user_id')
            );

        if ($currentRole === 'moderator') {
            $query->where('role', 'member');
        }

        $users = $query
            ->paginate(10)
            ->withQueryString()
            ->through(fn (TenantUser $membership) => [
                'id' => $membership->user?->id,
                'hash_id' => $membership->user?->hash_id,
                'name' => $membership->user?->name,
                'email' => $membership->user?->email,
                'role' => $membership->role,
            ]);

        return Inertia::render('admin/users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('admin/users/Create', [
            'roles' => $this->availableRoles($request->user(), $this->tenantContext->get()),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request, CreateUserAndSendSetPasswordLink $creator): RedirectResponse
    {
        $tenant = $this->tenantContext->get();
        $data = $request->validated();
        $role = $this->resolvedRole($request->user(), $tenant, $data['role']);

        $user = $creator->handle($data, $role);

        return to_route('admin.usuarios.edit', [
            'tenantSlug' => $tenant->slug,
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request,string $tenantSlug, User $user): Response
    {
        $tenant = $this->tenantContext->get();
        $membership = TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'hash_id' => $user->hash_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $membership->role,
                'access_started_at' => $user->email_verified_at
                    ? $membership->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i')
                    : null,
            ],
            'roles' => $this->availableRoles($request->user(), $tenant),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, string $tenantSlug, User $user): RedirectResponse
    {
        $tenant = $this->tenantContext->get();
        $data = $request->validated();
        $role = $this->resolvedRole($request->user(), $tenant, $data['role']);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('user_id', $user->id)
            ->update(['role' => $role]);

        return to_route('admin.usuarios.edit', [
            'tenantSlug' => $tenant?->slug,
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $tenantSlug, User $user): RedirectResponse
    {
        $tenant = $this->tenantContext->get();

        TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('user_id', $user->id)
            ->delete();

        return to_route('admin.usuarios.index', [
            'tenantSlug' => $tenant?->slug,
        ]);
    }

    public function resendInvite(
        Request $request,
        string $tenantSlug,
        User $user,
        SendSetPasswordLink $sendSetPasswordLink
    ): RedirectResponse {
        $this->authorize('update', $user);

        $tenant = $this->tenantContext->get();

        TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($user->email_verified_at) {
            logger()->info('Admin invite resend skipped (already verified).', [
                'tenant_id' => $tenant?->id,
                'actor_user_id' => $request->user()?->id,
                'target_user_id' => $user->id,
                'before' => [
                    'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
                ],
                'after' => [
                    'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
                ],
            ]);

            return to_route('admin.usuarios.edit', [
                'tenantSlug' => $tenant?->slug,
                'user' => $user,
            ]);
        }

        $before = [
            'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
        ];

        try {
            $sendSetPasswordLink->handle($user, $tenant);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return to_route('admin.usuarios.edit', [
                'tenantSlug' => $tenant?->slug,
                'user' => $user,
            ])->withErrors([
                'invite' => $exception->errors()['email'][0] ?? 'Não foi possível reenviar o convite.',
            ]);
        }

        logger()->info('Admin resent workspace invite email.', [
            'tenant_id' => $tenant?->id,
            'actor_user_id' => $request->user()?->id,
            'target_user_id' => $user->id,
            'before' => $before,
            'after' => [
                'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
            ],
        ]);

        return to_route('admin.usuarios.edit', [
            'tenantSlug' => $tenant?->slug,
            'user' => $user,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function availableRoles(?User $currentUser, $tenant): array
    {
        return $currentUser?->hasTenantRole('admin', $tenant)
            ? ['admin', 'moderator', 'member']
            : ['member'];
    }

    private function resolvedRole(?User $currentUser, $tenant, string $requestedRole): string
    {
        return $currentUser?->hasTenantRole('admin', $tenant) ? $requestedRole : 'member';
    }
}
