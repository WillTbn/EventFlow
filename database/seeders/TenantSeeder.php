<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Seed the application's demo tenant and initial admin user.
     */
    public function run(): void
    {
        $tenantName = env('DEMO_TENANT_NAME', 'Demo Workspace');
        $tenantSlug = env('DEMO_TENANT_SLUG', Str::slug($tenantName));

        $tenant = Tenant::firstOrCreate(
            ['slug' => $tenantSlug],
            [
                'name' => $tenantName,
                'plan' => 'free',
                'status' => 'active',
                'trial_ends_at' => null,
            ]
        );

        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminName = env('ADMIN_NAME', 'Admin');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        TenantUser::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $admin->id,
            ],
            [
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}
