<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Auth\AuthEventService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private readonly AuthEventService $authEvents)
    {
    }

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

        $user = User::create([
            'name' => $name,
            'first_name' => $parts[0] ?? $name,
            'last_name' => $parts[1] ?? '',
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'type' => UserType::USER->value,
            'active' => true,
        ]);

        RateLimiter::hit($request->throttleKey(), 60);

        Auth::login($user);
        $request->session()->regenerate();

        $this->authEvents->sendVerificationEmail($user);

        event(new Registered($user));

        return redirect()
            ->route('verification.notice')
            ->with('status', __('messages.account_created_verify'));
    }
}
