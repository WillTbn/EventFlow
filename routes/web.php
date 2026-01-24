<?php

use App\Http\Controllers\Public\EventsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('eventos', [EventsController::class, 'index'])->name('eventos.index');
Route::get('eventos/{event:slug}', [EventsController::class, 'show'])->name('eventos.show');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
