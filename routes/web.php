<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
})->name('landing');

Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'])) {
        abort(404);
    }

    session(['locale' => $locale]);
    session(['direction' => in_array($locale, ['ar', 'ur']) ? 'rtl' : 'ltr']);
    app()->setLocale($locale);

    return redirect()->back();
})->name('landing.locale');

Route::get('/office-manager-template', function () {
    return view('templates.cv.office-manager');
});

Route::get('/test-locale', function () {
    $currentLocale = app()->getLocale();
    $direction = session('direction', 'ltr');

    return response()->json([
        'current_locale' => $currentLocale,
        'direction' => $direction,
        'welcome_message' => __('name'),
        'dashboard_label' => __('dashboard'),
        'users_label' => __('users'),
        'profiles_label' => __('profiles'),
        'templates_label' => __('templates'),
        'navigation_label' => __('navigation'),
        'login_label' => __('login'),
        'email_label' => __('email'),
        'password_label' => __('password'),
    ]);
});

Route::get('/admin/switch-language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'])) {
        session(['locale' => $locale]);

        // Set direction for RTL languages
        if (in_array($locale, ['ar', 'ur'])) {
            session(['direction' => 'rtl']);
        } else {
            session(['direction' => 'ltr']);
        }

        // Force the app to use the new locale
        app()->setLocale($locale);
    }

    $back = url()->previous('/');
    // If the previous URL would route us back to admin while logged out, fall back to /admin/login
    if (str_contains($back, '/admin') && ! auth()->check()) {
        $back = '/admin/login';
    }
    return redirect()->to($back);
})->middleware(['web', \App\Http\Middleware\SetLocale::class]);

Route::get('/profile/{id}', [\App\Http\Controllers\ProfilePreviewController::class, 'preview'])
    ->name('profile.preview');

Route::get('/cover-letter/{id}', [\App\Http\Controllers\CoverLetterPreviewController::class, 'preview'])
    ->name('cover-letter.preview');
