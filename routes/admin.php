<?php

use App\Http\Controllers\Admin\EventPhotosController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\WorkspaceController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'setCurrentTenant', 'tenantRole:admin,moderator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            $from = now()->startOfMonth()->subMonth();
            $to = now()->endOfMonth()->addMonths(2);

            $events = Event::query()
                ->whereNotNull('starts_at')
                ->whereBetween('starts_at', [$from, $to])
                ->withCount('rsvps')
                ->orderBy('starts_at')
                ->take(120)
                ->get()
                ->map(fn (Event $event) => [
                    'id' => $event->id,
                    'hash_id' => $event->hash_id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'starts_at' => $event->starts_at?->format('Y-m-d H:i'),
                    'ends_at' => $event->ends_at?->format('Y-m-d H:i'),
                    'status' => $event->status,
                    'is_public' => $event->is_public,
                    'capacity' => $event->capacity,
                    'rsvp_count' => $event->rsvps_count,
                ])
                ->values()
                ->all();

            return Inertia::render('Dashboard', [
                'events' => $events,
            ]);
        })->name('dashboard');

        Route::resource('eventos', EventsController::class)
            ->parameters(['eventos' => 'event'])
            ->scoped(['event' => 'hash_id'])
            ->except(['show']);

        Route::post('eventos/{event:hash_id}/foto-principal', [EventPhotosController::class, 'updateMain'])
            ->name('eventos.foto-principal');

        Route::post('eventos/{event:hash_id}/fotos', [EventPhotosController::class, 'store'])
            ->name('eventos.fotos');

        Route::resource('usuarios', UsersController::class)
            ->parameters(['usuarios' => 'user'])
            ->scoped(['user' => 'hash_id'])
            ->except(['show']);

        Route::post('usuarios/{user:hash_id}/resend-invite', [UsersController::class, 'resendInvite'])
            ->name('usuarios.resend-invite');

        Route::get('workspace', [WorkspaceController::class, 'edit'])
            ->middleware('tenantRole:admin')
            ->name('workspace.edit');

        Route::put('workspace', [WorkspaceController::class, 'update'])
            ->middleware('tenantRole:admin')
            ->name('workspace.update');
    });
