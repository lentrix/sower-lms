<?php

use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::get('/dashboard', [DashboardController::class,'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/borrowers/create',[BorrowerController::class, 'create'])->name('borrowers.create');
    Route::get('/borrowers/search', [BorrowerController::class, 'search']);
    Route::get('/borrowers/edit/{borrower}',[BorrowerController::class, 'edit'])->name('borrowers.edit');
    Route::get('/borrowers/{borrower}',[BorrowerController::class, 'show'])->name('borrowers.show');
    Route::put('/borrowers/{borrower}',[BorrowerController::class, 'update']);
    Route::get('/borrowers',[BorrowerController::class, 'index'])->name('borrowers');
    Route::post('/borrowers', [BorrowerController::class, 'store']);

    Route::get('/loans', [LoanController::class, 'index'])->name('loans');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('loans.update');
    Route::post('/loans/set-status/{status}/{loan}', [LoanController::class, 'setStatus'])->name('loans.set-status');
    Route::get('/loans/edit/{loan}', [LoanController::class, 'edit'])->name('loans.edit');
    Route::get('/loans/resync/{loan}', [LoanController::class, 'resyncAmortization'])->name('loans.resync');
    Route::get('/loans/sync-balance/{loan}', [LoanController::class, 'syncWithBalance'])->name('loans.sync-balance');
    Route::get('/loans/create/{borrower}', [LoanController::class, 'create'])->name('loans.create-with-borrower');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/payee/{borrower}', [PaymentController::class, 'pay'])->name('payments.pay');

    Route::get('/reports', function() {
        return Inertia::render('Reports/Index');
    })->name('reports');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::group(['middleware'=>['can:manage users']], function(){
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users',[UserController::class,'store']);
        Route::get('/users', [UserController::class, 'index'])->name('users');
    });
});

require __DIR__.'/auth.php';
