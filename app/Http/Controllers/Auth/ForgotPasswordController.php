<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly EmailOtpService $otp)
    {
    }

    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = strtolower((string) $request->input('email'));

        $this->otp->issuePasswordReset($email);

        return back()->with('status', __('messages.reset_code_sent_generic'));
    }
}
