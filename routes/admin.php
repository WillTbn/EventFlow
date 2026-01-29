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
            ->except(['show']);

        Route::post('eventos/{event}/foto-principal', [EventPhotosController::class, 'updateMain'])
            ->name('eventos.foto-principal');

        Route::post('eventos/{event}/fotos', [EventPhotosController::class, 'store'])
            ->name('eventos.fotos');

        Route::resource('usuarios', UsersController::class)
            ->parameters(['usuarios' => 'user'])
            ->except(['show']);
    });
