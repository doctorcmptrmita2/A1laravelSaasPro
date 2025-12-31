<?php

use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\ProxyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// LiteLLM Proxy Endpoints (Public - requires API key in header)
Route::prefix('v1')->group(function () {
    Route::post('/chat/completions', [ProxyController::class, 'chatCompletions']);
    Route::post('/completions', [ProxyController::class, 'completions']);
    Route::post('/embeddings', [ProxyController::class, 'embeddings']);
});

// Authenticated API Routes (Dashboard API)
Route::middleware(['auth:sanctum'])->group(function () {
    // API Keys Management
    Route::apiResource('api-keys', ApiKeyController::class);
    
    // Dashboard Stats (will be added later)
    // Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    // Route::get('/dashboard/usage', [DashboardController::class, 'usage']);
    // Route::get('/dashboard/analytics', [DashboardController::class, 'analytics']);
});

