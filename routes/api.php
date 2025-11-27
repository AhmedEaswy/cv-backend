<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CVController;

Route::prefix('v1')->group(function () {
    // Public auth routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/auth/reset-token', [AuthController::class, 'verifyResetToken']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // Public CV route (create only)
    Route::post('/cvs', [CVController::class, 'store']);

    // Protected CV routes (authenticated users only)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cvs', [CVController::class, 'index']);
        Route::get('/cvs/{id}', [CVController::class, 'show']);
        Route::put('/cvs/{id}', [CVController::class, 'update']);
        Route::delete('/cvs/{id}', [CVController::class, 'destroy']);
    });
});

