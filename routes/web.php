<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);

        // Set direction for RTL languages
        if ($locale === 'ar') {
            session(['direction' => 'rtl']);
        } else {
            session(['direction' => 'ltr']);
        }

        // Force the app to use the new locale
        app()->setLocale($locale);
    }

    return redirect()->back();
})->middleware(['auth', 'web', \App\Http\Middleware\SetLocale::class]);
