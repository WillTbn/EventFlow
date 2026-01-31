<?php

namespace App\Actions\Photos;

use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreTenantLogo
{
    public function __construct(private StoreImageVariants $storeImageVariants)
    {
    }

    /**
     * Store the tenant logo and generate variants.
     */
    public function handle(Tenant $tenant, UploadedFile $logo): Tenant
    {
        $oldPaths = array_filter([
            $tenant->logo_path,
            $tenant->logo_medium_path,
            $tenant->logo_thumb_path,
        ]);

        $paths = $this->storeImageVariants->handle(
            $logo,
            'tenants/' . $tenant->id . '/logo',
            'logo'
        );

        $tenant->forceFill([
            'logo_path' => $paths['original'],
            'logo_medium_path' => $paths['medium'],
            'logo_thumb_path' => $paths['thumb'],
        ])->save();

        Storage::disk('public')->delete($oldPaths);

        return $tenant;
    }
}
