<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Actions\Users\CreateUserAndSendSetPasswordLink;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $query = TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('status', 'active')
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
    public function edit(Request $request, User $user): Response
    {
        $tenant = $this->tenantContext->get();
        $membership = TenantUser::query()
            ->where('tenant_id', $tenant?->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $membership->role,
            ],
            'roles' => $this->availableRoles($request->user(), $tenant),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
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
    public function destroy(User $user): RedirectResponse
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
