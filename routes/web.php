<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaterRateBracketController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'force.password.change'])
    ->name('dashboard');

Route::post('/dashboard/send-reminders', [App\Http\Controllers\DashboardController::class, 'sendPaymentReminders'])
    ->middleware(['auth', 'verified', 'force.password.change'])
    ->name('dashboard.send-reminders');

// Force Password Change Routes (must be before the main auth group)
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [ForcePasswordChangeController::class, 'show'])
        ->name('password.force-change');
    Route::post('/password/change', [ForcePasswordChangeController::class, 'update'])
        ->name('password.force-change.update');
});

Route::middleware(['auth', 'force.password.change'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Settings Routes (Admin only)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Water Rate Brackets (Admin only)
    Route::resource('rate-brackets', WaterRateBracketController::class)->except(['show', 'create', 'edit']);



    // Block Assignments (Admin only)
    Route::get('/settings/block-assignments', [BlockController::class, 'assignments'])->name('settings.block-assignments');
    Route::put('/settings/block-assignments/{user}', [BlockController::class, 'updateAssignments'])->name('settings.block-assignments.update');

    // Announcements (Admin only)
    Route::resource('announcements', AnnouncementController::class);
    Route::patch('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');

    // Users Management (Admin only - Staff users, modals for create/edit)
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);

    // Consumers Management (Admin only, modals for create/edit)
    Route::resource('consumers', ConsumerController::class)->except(['create', 'edit']);

    // Materials Management (Admin only)
    Route::resource('materials', MaterialController::class)->except(['show', 'create', 'edit']);
    Route::post('/materials/{material}/add-stock', [MaterialController::class, 'addStock'])->name('materials.add-stock');
    Route::get('/materials/stock-movements', [MaterialController::class, 'stockMovements'])->name('materials.stock-movements');

    // Maintenance Requests (All authenticated users, with role-based filtering)
    Route::resource('maintenance-requests', MaintenanceRequestController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('/maintenance-requests/{maintenance_request}/status', [MaintenanceRequestController::class, 'updateStatus'])
        ->name('maintenance-requests.update-status');
    Route::post('/maintenance-requests/{maintenance_request}/materials', [MaintenanceRequestController::class, 'addMaterial'])
        ->name('maintenance-requests.add-material');
    Route::delete('/maintenance-requests/{maintenance_request}/materials/{material}', [MaintenanceRequestController::class, 'removeMaterial'])
        ->name('maintenance-requests.remove-material');

    // Meter Readings (Admin, Meter Reader)
    Route::resource('meter-readings', MeterReadingController::class)->except(['show', 'create', 'edit']);
    Route::get('/meter-readings/previous/{consumer}', [MeterReadingController::class, 'getPreviousReading'])
        ->name('meter-readings.previous');

    // Bills Management
    Route::get('/bills', [App\Http\Controllers\BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [App\Http\Controllers\BillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/print', [App\Http\Controllers\BillController::class, 'print'])->name('bills.print');

    // Payments Management (Admin & Cashier)
    Route::get('/payments', [App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/bills/{bill}/payments', [App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/receipt', [App\Http\Controllers\PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments-summary', [App\Http\Controllers\PaymentController::class, 'dailySummary'])->name('payments.daily-summary');

    // Reports (Admin & Cashier for bill-related, Admin only for others)
    Route::prefix('reports')->name('reports.')->group(function () {
        // Collections Report (Admin & Cashier)
        Route::get('/collections', [App\Http\Controllers\ReportController::class, 'collections'])->name('collections');
        Route::get('/collections/export/{format}', [App\Http\Controllers\ReportController::class, 'collectionsExport'])->name('collections.export');

        // Billing Summary (Admin & Cashier)
        Route::get('/billing-summary', [App\Http\Controllers\ReportController::class, 'billingSummary'])->name('billing-summary');
        Route::get('/billing-summary/export/{format}', [App\Http\Controllers\ReportController::class, 'billingSummaryExport'])->name('billing-summary.export');

        // Arrears Report (Admin only)
        Route::get('/arrears', [App\Http\Controllers\ReportController::class, 'arrears'])->name('arrears');
        Route::get('/arrears/export/{format}', [App\Http\Controllers\ReportController::class, 'arrearsExport'])->name('arrears.export');

        // Consumption Report (Admin only)
        Route::get('/consumption', [App\Http\Controllers\ReportController::class, 'consumption'])->name('consumption');
        Route::get('/consumption/export/{format}', [App\Http\Controllers\ReportController::class, 'consumptionExport'])->name('consumption.export');

        // Consumer Masterlist (Admin only)
        Route::get('/consumer-masterlist', [App\Http\Controllers\ReportController::class, 'consumerMasterlist'])->name('consumer-masterlist');
        Route::get('/consumer-masterlist/export/{format}', [App\Http\Controllers\ReportController::class, 'consumerMasterlistExport'])->name('consumer-masterlist.export');
    });
});

require __DIR__.'/auth.php';
