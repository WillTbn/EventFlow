<?php

use App\Http\Controllers\Central\WorkspacesController;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function (Request $request) {
    $user = $request->user();
    $dashboardUrl = null;

    if ($user) {
        $currentTenantId = $request->session()->get(TenantContext::SESSION_KEY);
        $membershipQuery = $user->tenantMemberships()
            ->with('tenant')
            ->where('status', 'active');

        $membership = null;

        if ($currentTenantId) {
            $membership = (clone $membershipQuery)
                ->where('tenant_id', $currentTenantId)
                ->first();
        }

        if (! $membership) {
            $membership = $membershipQuery->orderBy('id')->first();
        }

        if ($membership?->tenant) {
            $dashboardUrl = '/t/'.$membership->tenant->slug.'/admin';
        }
    }

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'dashboardUrl' => $dashboardUrl,
    ]);
})->name('home');

Route::get('pricing', function () {
    return Inertia::render('Pricing');
})->name('pricing');

Route::middleware(['auth'])->group(function () {
    Route::get('workspaces', [WorkspacesController::class, 'index'])->name('workspaces');
});

require __DIR__.'/tenant.php';
