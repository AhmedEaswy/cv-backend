<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\AuthEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly AuthEventService $authEvents)
    {
    }

    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = strtolower((string) $request->input('email'));

        // Generic response — never reveal whether the address is on file.
        $genericResponse = back()->with('status', __('messages.reset_link_sent_generic'));

        if (! $this->authEvents->canSendResetLink($email)) {
            return $genericResponse;
        }

        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        $this->authEvents->markResetLinkSent($email);

        return $genericResponse;
    }
}
