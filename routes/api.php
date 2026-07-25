<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyticsClickController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoverLetterController;
use App\Http\Controllers\Api\CVController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Middleware\AnalyticsMiddleware;

Route::prefix('v1')->group(function () {
    // Public click-tracking endpoint (landing page App Store / Play Store badges).
    // Intentionally NOT inside the AnalyticsMiddleware group — the controller
    // records its own event with a specific action_type, and we don't want a
    // duplicate null-action row.
    Route::post('/analytics/click', [AnalyticsClickController::class, 'store']);
});

Route::prefix('v1')->middleware([AnalyticsMiddleware::class])->group(function () {
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

    // Social auth routes
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback']);

    // Public shares routes
    Route::get('/shares/templates', [ShareController::class, 'templates']);

    // Public CV routes
    Route::post('/cvs', [CVController::class, 'store']);
    Route::post('/cvs/print', [CVController::class, 'print']);

    // Public cover letter routes
    Route::get('/cover-letters/templates', [CoverLetterController::class, 'templates']);
    Route::post('/cover-letters', [CoverLetterController::class, 'store']);
    Route::post('/cover-letters/print', [CoverLetterController::class, 'print']);

    // Protected CV routes (authenticated users only)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cvs', [CVController::class, 'index']);
        Route::get('/cvs/{id}', [CVController::class, 'show']);
        Route::put('/cvs/{id}', [CVController::class, 'update']);
        Route::delete('/cvs/{id}', [CVController::class, 'destroy']);
    });

    // Protected cover letter routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cover-letters', [CoverLetterController::class, 'index']);
        Route::get('/cover-letters/{id}', [CoverLetterController::class, 'show']);
        Route::put('/cover-letters/{id}', [CoverLetterController::class, 'update']);
        Route::delete('/cover-letters/{id}', [CoverLetterController::class, 'destroy']);
    });
});
