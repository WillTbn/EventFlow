<?php

use App\Http\Controllers\Public\EventsController;
use App\Http\Controllers\Public\WorkspaceController;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('t/{tenantSlug}')
    ->group(function () {
        Route::middleware(['setCurrentTenant'])->group(function () {
            Route::get('/', [WorkspaceController::class, 'show'])->name('workspace.show');
            Route::get('eventos', [EventsController::class, 'index'])->name('eventos.index');
            Route::get('eventos/{event:hash_id}', [EventsController::class, 'show'])->name('eventos.show');
        });

        Route::middleware(['auth', 'setCurrentTenant'])->get('ping', function (Request $request) {
            $tenant = app(TenantContext::class)->get();

            return response()->json([
                'tenant' => $tenant ? [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'plan' => $tenant->plan,
                    'status' => $tenant->status,
                ] : null,
                'user' => $request->user(),
            ]);
        })->name('tenant.ping');

        require __DIR__.'/settings.php';
        require __DIR__.'/admin.php';
    });
