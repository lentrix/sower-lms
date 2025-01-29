<?php

use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/borrowers/create',[BorrowerController::class, 'create'])->name('borrowers.create');
    Route::post('/borrowers/search', [BorrowerController::class, 'search']);
    Route::get('/borrowers/edit/{borrower}',[BorrowerController::class, 'edit'])->name('borrowers.edit');
    Route::get('/borrowers/{borrower}',[BorrowerController::class, 'show'])->name('borrowers.show');
    Route::put('/borrowers/{borrower}',[BorrowerController::class, 'update']);
    Route::get('/borrowers',[BorrowerController::class, 'index'])->name('borrowers');
    Route::post('/borrowers', [BorrowerController::class, 'store']);

    Route::get('/loans', function() {
        return Inertia::render('Loans/Index');
    })->name('loans');

    Route::get('/payments', function() {
        return Inertia::render('Payments/Index');
    })->name('payments');

    Route::get('/reports', function() {
        return Inertia::render('Reports/Index');
    })->name('reports');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
