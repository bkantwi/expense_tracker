<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseAttachmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('budgets', BudgetController::class);

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');

    Route::resource('recurrences', RecurrenceController::class);

    Route::post('/expenses/{expense}/attachments', [ExpenseAttachmentController::class, 'store'])->name('expenses.attachments.store');
    Route::get('/attachments/{attachment}/download', [ExpenseAttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [ExpenseAttachmentController::class, 'destroy'])->name('attachments.destroy');
});

require __DIR__.'/auth.php';
