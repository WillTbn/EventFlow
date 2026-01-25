<?php

use App\Http\Controllers\Admin\EventsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin|moderator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('eventos', EventsController::class)
            ->parameters(['eventos' => 'event'])
            ->except(['show']);
    });
