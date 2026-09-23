<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $data = $request->validated();

        $name = trim($data['name']);
        $parts = preg_split('/\s+/u', $name, 2) ?: [$name];
        $email = strtolower($data['email']);

        $user = User::create([
            'name' => $name,
            'first_name' => $parts[0] ?? $name,
            'last_name' => $parts[1] ?? '',
            'email' => $email,
            'password' => $data['password'],
            'type' => UserType::USER->value,
            'active' => true,
        ]);

        RateLimiter::hit($request->throttleKey(), 60);

        Auth::login($user);
        $request->session()->regenerate();

        event(new Registered($user));

        return redirect()
            ->route('verification.notice')
            ->with('status', __('messages.account_created_verify'));
    }
}
