<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        $query = User::query()->with('roles')->orderBy('name');

        if ($request->user()->hasRole('moderator')) {
            $query->role('member');
        }

        $users = $query
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
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
            'roles' => $this->availableRoles($request->user()),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = $this->resolvedRole($request->user(), $data['role']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Role::firstOrCreate(['name' => $role]);
        $user->assignRole($role);

        return to_route('admin.usuarios.edit', $user);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request, User $user): Response
    {
        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
            ],
            'roles' => $this->availableRoles($request->user()),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $role = $this->resolvedRole($request->user(), $data['role']);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        Role::firstOrCreate(['name' => $role]);
        $user->syncRoles([$role]);

        return to_route('admin.usuarios.edit', $user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return to_route('admin.usuarios.index');
    }

    /**
     * @return array<int, string>
     */
    private function availableRoles(User $currentUser): array
    {
        return $currentUser->hasRole('admin')
            ? ['admin', 'moderator', 'member']
            : ['member'];
    }

    private function resolvedRole(User $currentUser, string $requestedRole): string
    {
        return $currentUser->hasRole('admin') ? $requestedRole : 'member';
    }
}
