<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseAttachmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TransferController;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
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

    // Mark all as read
    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('notifications.read-all');
    // (Optional) mark one as read on click
    Route::post('/notifications/{notification}/read', function (Request $request, DatabaseNotification $notification) {
        abort_unless($notification->notifiable_id === $request->user()->id, 403);
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }
        return back();
    })->name('notifications.read');

//    CSV Import
    Route::get('/imports/expenses', [ExpenseImportController::class, 'index'])->name('expenses.import.index');
    Route::post('/imports/expenses/preview', [ExpenseImportController::class, 'preview'])->name('expenses.import.preview');
    Route::post('/imports/expenses/commit', [ExpenseImportController::class, 'commit'])->name('expenses.import.commit');

    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::patch('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
});

require __DIR__.'/auth.php';
