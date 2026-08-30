<?php

use App\Http\Controllers\AgentDiscoveryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
| The user-facing frontend (landing, auth, portal) is now served by the
| Nuxt 3 app at / (configured via Composer "dev" script running both
| `php artisan serve` and `cd frontend && pnpm dev`). Laravel here only
| owns:
|   - the Filament admin panel at /admin/*
|   - public profile / cover letter / template previews (URLs that
|     must work even when Nuxt is down or off)
|   - the locale switcher used by the email templates and admin UI
|   - the contact-message webhook from public profile pages
*/

// Locale switcher — kept because the Filament admin and email templates
// still resolve app()->getLocale() from the session.
Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'])) {
        abort(404);
    }

    session(['locale' => $locale]);
    session(['direction' => in_array($locale, ['ar', 'ur']) ? 'rtl' : 'ltr']);
    app()->setLocale($locale);

    return redirect()->back();
})->name('landing.locale');

// Admin-only language switcher (Filament lives in /admin/*).
Route::get('/admin/switch-language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'])) {
        session(['locale' => $locale]);

        if (in_array($locale, ['ar', 'ur'])) {
            session(['direction' => 'rtl']);
        } else {
            session(['direction' => 'ltr']);
        }

        app()->setLocale($locale);
    }

    $back = url()->previous('/');
    if (str_contains($back, '/admin') && ! auth()->check()) {
        $back = '/admin/login';
    }

    return redirect()->to($back);
})->middleware(['web', \App\Http\Middleware\SetLocale::class]);

// Public previews — kept on Laravel because they need to be shareable
// links that work without any client-side JS.
Route::get('/profile/{id}', [\App\Http\Controllers\ProfilePreviewController::class, 'preview'])
    ->name('profile.preview');

Route::get('/cover-letter/{id}', [\App\Http\Controllers\CoverLetterPreviewController::class, 'preview'])
    ->name('cover-letter.preview');

Route::get('/u/{slug}', [\App\Http\Controllers\PublicProfilePreviewController::class, 'preview'])
    ->name('public-profile.preview');

Route::post('/u/{slug}/contact', [\App\Http\Controllers\Public\ContactMessageController::class, 'store'])
    ->name('public-profile.contact')
    ->middleware('throttle:5,1');

// Template test pages — used by the landing template carousel.
Route::get('/test/cv/{template}', [\App\Http\Controllers\TemplateTestController::class, 'cv'])
    ->name('templates.cv.test');

Route::get('/test/cover-letter/{template}', [\App\Http\Controllers\TemplateTestController::class, 'coverLetter'])
    ->name('templates.cover-letter.test');

Route::get('/test/public-profile/{template}', [\App\Http\Controllers\TemplateTestController::class, 'publicProfile'])
    ->name('templates.public-profile.test');

Route::get('/llms.txt', [AgentDiscoveryController::class, 'llms']);
Route::get('/skill.md', [AgentDiscoveryController::class, 'skill']);
Route::get('/openapi.json', [AgentDiscoveryController::class, 'openapi']);
Route::get('/.well-known/mcp.json', [AgentDiscoveryController::class, 'mcp']);

// Catch-all for any other /portal, /auth, or top-level request —
// return a 404 so Laravel doesn't accidentally render a Blade view.
// The Nuxt dev server on :3000 handles those routes in development;
// in production, Nginx routes /portal and /auth to the Nuxt SSR
// server and only forwards /api and /admin to Laravel.
Route::fallback(function () {
    abort(404);
});
