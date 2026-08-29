<?php

use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Portal\CoverLetterController;
use App\Http\Controllers\Portal\CvController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\PublicProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authenticated Portal Routes
|--------------------------------------------------------------------------
| Every route here requires an active, verified user. Inactive accounts
| are signed out, unverified users land on the verification notice.
*/

Route::middleware(['auth', 'verified'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // CVs
    Route::get('/cvs', [CvController::class, 'index'])->name('cvs.index');
    Route::get('/cvs/create', [CvController::class, 'create'])->name('cvs.create');
    Route::post('/cvs', [CvController::class, 'store'])->name('cvs.store');
    Route::get('/cvs/{id}/edit', [CvController::class, 'edit'])->name('cvs.edit');
    Route::put('/cvs/{id}', [CvController::class, 'update'])->name('cvs.update');
    Route::delete('/cvs/{id}', [CvController::class, 'destroy'])->name('cvs.destroy');
    Route::post('/cvs/{id}/duplicate', [CvController::class, 'duplicate'])->name('cvs.duplicate');
    Route::get('/cvs/{id}/preview', [CvController::class, 'pdf'])->name('cvs.preview');

    // Cover Letters
    Route::get('/cover-letters', [CoverLetterController::class, 'index'])->name('cover-letters.index');
    Route::get('/cover-letters/create', [CoverLetterController::class, 'create'])->name('cover-letters.create');
    Route::post('/cover-letters', [CoverLetterController::class, 'store'])->name('cover-letters.store');
    Route::get('/cover-letters/{id}/edit', [CoverLetterController::class, 'edit'])->name('cover-letters.edit');
    Route::put('/cover-letters/{id}', [CoverLetterController::class, 'update'])->name('cover-letters.update');
    Route::delete('/cover-letters/{id}', [CoverLetterController::class, 'destroy'])->name('cover-letters.destroy');
    Route::post('/cover-letters/{id}/duplicate', [CoverLetterController::class, 'duplicate'])
        ->name('cover-letters.duplicate');

    // Public profile
    Route::get('/public-profile', [PublicProfileController::class, 'edit'])->name('public-profile.edit');
    Route::post('/public-profile', [PublicProfileController::class, 'store'])->name('public-profile.store');
    Route::put('/public-profile', [PublicProfileController::class, 'update'])->name('public-profile.update');
    Route::delete('/public-profile', [PublicProfileController::class, 'destroy'])->name('public-profile.destroy');
    Route::get('/public-profile/inbox', [PublicProfileController::class, 'contactMessages'])
        ->name('public-profile.inbox');
    Route::post('/public-profile/inbox/{id}/read', [PublicProfileController::class, 'markRead'])
        ->name('public-profile.inbox.read');

    // Settings
    Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('settings.profile');
    Route::put('/settings/profile', [ProfileController::class, 'update']);
    Route::put('/settings/password', [PasswordController::class, 'update'])->name('settings.password');
});
