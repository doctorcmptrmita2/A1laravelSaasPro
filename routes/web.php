<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'identify.tenant'])->name('dashboard');

// API Keys Management
Route::middleware(['auth', 'verified', 'identify.tenant'])->group(function () {
    Route::get('/api-keys', function () {
        return Inertia::render('ApiKeys/Index');
    })->name('api-keys.page');
    
    // API endpoints (web middleware grubunu kullanarak session-based authentication)
    Route::prefix('api')->group(function () {
        // API Keys Management
        Route::apiResource('api-keys', \App\Http\Controllers\Api\ApiKeyController::class);
        
        // Dashboard Stats
        Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);
        Route::get('/dashboard/usage', [\App\Http\Controllers\Api\DashboardController::class, 'usage']);
        Route::get('/dashboard/analytics', [\App\Http\Controllers\Api\DashboardController::class, 'analytics']);
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
