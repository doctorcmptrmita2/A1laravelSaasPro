<?php

use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\DashboardController;
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

// Authenticated API Routes moved to web.php for session-based authentication
// These routes are now in routes/web.php under /api prefix with web middleware

