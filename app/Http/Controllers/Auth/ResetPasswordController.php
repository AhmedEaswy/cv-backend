<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\Auth\EmailOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(private readonly EmailOtpService $otp)
    {
    }

    public function show(Request $request, ?string $token = null): View
    {
        return view('auth.reset-password', [
            'email' => (string) $request->query('email', old('email', '')),
            'code' => (string) ($request->query('code', old('code', ''))),
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $email = strtolower((string) $request->input('email'));
        $result = $this->otp->verify(
            $email,
            EmailOtp::PURPOSE_PASSWORD_RESET,
            (string) $request->input('code')
        );

        if ($result !== 'ok') {
            $message = $result === 'expired'
                ? __('messages.otp_expired')
                : __('messages.otp_invalid');

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => $message]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['email' => __('messages.user_not_found')]);
        }

        $user->forceFill([
            'password' => $request->input('password'),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', __('messages.password_reset_success'));
    }
}
