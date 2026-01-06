<?php

use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaterRateBracketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Settings Routes (Admin only)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Water Rate Brackets (Admin only)
    Route::resource('rate-brackets', WaterRateBracketController::class)->except(['show', 'create', 'edit']);

    // Users Management (Admin only - Staff users, modals for create/edit)
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);

    // Consumers Management (Admin only, modals for create/edit)
    Route::resource('consumers', ConsumerController::class)->except(['create', 'edit']);
    // Meter Readings (Admin, Meter Reader)
    Route::resource('meter-readings', MeterReadingController::class)->except(['show', 'create', 'edit']);
    Route::get('/meter-readings/previous/{consumer}', [MeterReadingController::class, 'getPreviousReading'])
        ->name('meter-readings.previous');

    // Bills Management
    Route::get('/bills', [App\Http\Controllers\BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [App\Http\Controllers\BillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/print', [App\Http\Controllers\BillController::class, 'print'])->name('bills.print');
});

require __DIR__.'/auth.php';
