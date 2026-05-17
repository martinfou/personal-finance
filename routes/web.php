<?php

use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\SavingGoalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('transactions', TransactionController::class);
    Route::prefix('csv-import')->name('csv-import.')->group(function () {
        Route::get('/', [CsvImportController::class, 'index'])->name('index');
        Route::post('/preview', [CsvImportController::class, 'preview'])->name('preview');
        Route::post('/import', [CsvImportController::class, 'import'])->name('import');
    });
    Route::resource('recurring', RecurringTransactionController::class);
    Route::post('/recurring/process', [RecurringTransactionController::class, 'process'])->name('recurring.process');
    Route::resource('goals', SavingGoalController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
