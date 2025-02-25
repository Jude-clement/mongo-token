<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\MongoDBAuthMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/users', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(MongoDBAuthMiddleware::class)->group(function () {
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Invalid action'
    ], 401);
});