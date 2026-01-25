<?php

use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin|moderator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('eventos', EventsController::class)
            ->parameters(['eventos' => 'event'])
            ->except(['show']);

        Route::resource('usuarios', UsersController::class)
            ->parameters(['usuarios' => 'user'])
            ->except(['show']);
    });
