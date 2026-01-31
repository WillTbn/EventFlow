<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Str;

class UpdateTenantProfile
{
    /**
     * Update the tenant name and regenerate a unique slug.
     */
    public function handle(Tenant $tenant, string $name): Tenant
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Tenant::query()
                ->where('slug', $slug)
                ->where('id', '!=', $tenant->id)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $tenant->forceFill([
            'name' => $name,
            'slug' => $slug,
        ])->save();

        return $tenant;
    }
}
