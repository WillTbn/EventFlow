<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'hash_id';
    }

    /**
     * Get the events created by the user.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Tenants the user belongs to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
            ->using(TenantUser::class)
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    /**
     * Membership records for the user.
     */
    public function tenantMemberships(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    /**
     * Get the user's role for a tenant.
     */
    public function tenantRole(?Tenant $tenant = null): ?string
    {
        $tenant ??= app(TenantContext::class)->get();

        if (! $tenant) {
            return null;
        }

        return $this->tenantMemberships()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->value('role');
    }

    /**
     * Check if the user has one of the given roles for a tenant.
     *
     * @param array<int, string>|string $roles
     */
    public function hasTenantRole(array|string $roles, ?Tenant $tenant = null): bool
    {
        $role = $this->tenantRole($tenant);

        if (! $role) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($role, $roles, true);
    }
}
