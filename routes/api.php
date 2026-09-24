<?php

use App\Http\Controllers\Api\AgentTokenController;
use App\Http\Controllers\Api\AiSettingController;
use App\Http\Controllers\Api\AnalyticsClickController;
use App\Http\Controllers\Api\AtsCheckController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoverLetterController;
use App\Http\Controllers\Api\CVController;
use App\Http\Controllers\Api\LinkedInCvController;
use App\Http\Controllers\Api\PortalStatsController;
use App\Http\Controllers\Api\PublicProfileController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Middleware\AnalyticsMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public click-tracking endpoint (landing page App Store / Play Store badges).
    // Intentionally NOT inside the AnalyticsMiddleware group — the controller
    // records its own event with a specific action_type, and we don't want a
    // duplicate null-action row.
    Route::post('/analytics/click', [AnalyticsClickController::class, 'store']);
});

Route::prefix('v1')->middleware([AnalyticsMiddleware::class])->group(function () {
    // Public auth routes
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail'])->middleware('throttle:10,1');
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // Social auth routes
    Route::post('/auth/google', [SocialAuthController::class, 'google'])->middleware('throttle:10,1');
    Route::post('/auth/linkedin', [SocialAuthController::class, 'linkedin'])->middleware('throttle:10,1');
    Route::post('/auth/apple', [SocialAuthController::class, 'apple'])->middleware('throttle:10,1');
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback']);

    // Public shares routes
    Route::get('/shares/templates', [ShareController::class, 'templates']);

    // Public CV routes (optional Bearer / anonymous install id)
    Route::post('/cvs', [CVController::class, 'store']);
    Route::post('/cvs/print', [CVController::class, 'print']);
    Route::put('/cvs/{id}', [CVController::class, 'update']);
    Route::post('/cvs/ats-check', [AtsCheckController::class, 'check']);
    Route::post('/cvs/ats-check/upload', [AtsCheckController::class, 'checkUpload']);

    // Public cover letter routes
    Route::get('/cover-letters/templates', [CoverLetterController::class, 'templates']);
    Route::post('/cover-letters', [CoverLetterController::class, 'store']);
    Route::post('/cover-letters/print', [CoverLetterController::class, 'print']);
    Route::put('/cover-letters/{id}', [CoverLetterController::class, 'update']);

    // Public profile template list
    Route::get('/public-profiles/templates', [PublicProfileController::class, 'templates']);

    // Protected CV routes (authenticated users only)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cvs', [CVController::class, 'index']);
        Route::post('/cvs/import/linkedin', [LinkedInCvController::class, 'store'])->middleware('throttle:10,1');
        Route::post('/cvs/{id}/duplicate', [CVController::class, 'duplicate']);
        Route::get('/cvs/{id}', [CVController::class, 'show']);
        Route::delete('/cvs/{id}', [CVController::class, 'destroy']);
    });

    // Protected cover letter routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cover-letters', [CoverLetterController::class, 'index']);
        Route::get('/cover-letters/{id}', [CoverLetterController::class, 'show']);
        Route::delete('/cover-letters/{id}', [CoverLetterController::class, 'destroy']);
    });

    // Protected public profile routes (one profile per user)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/public-profiles', [PublicProfileController::class, 'show']);
        Route::post('/public-profiles', [PublicProfileController::class, 'store']);
        Route::put('/public-profiles', [PublicProfileController::class, 'update']);
        Route::delete('/public-profiles', [PublicProfileController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/portal/stats', PortalStatsController::class);

        Route::get('/agent-tokens', [AgentTokenController::class, 'index']);
        Route::post('/agent-tokens', [AgentTokenController::class, 'store']);
        Route::delete('/agent-tokens/{id}', [AgentTokenController::class, 'destroy']);

        Route::get('/ai-settings', [AiSettingController::class, 'show']);
        Route::put('/ai-settings', [AiSettingController::class, 'update']);
    });
});
