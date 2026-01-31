<?php

use App\Http\Controllers\Admin\EventPhotosController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'setCurrentTenant', 'tenantRole:admin,moderator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return Inertia::render('Dashboard');
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
    });
