<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\Auth\EmailOtpService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function __construct(private readonly EmailOtpService $otp)
    {
    }

    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('portal.dashboard'));
        }

        $cooldown = $this->otp->secondsUntilResend(
            strtolower((string) $user->email),
            EmailOtp::PURPOSE_REGISTER
        );

        return view('auth.verify-email', [
            'resendCooldown' => $cooldown,
        ]);
    }

    /**
     * Legacy signed-link verification — kept for old emails in flight.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1((string) $user->email), (string) $hash)) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => __('messages.otp_invalid')]);
        }

        if (! $request->hasValidSignature()) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => __('messages.otp_expired')]);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if (Auth::id() !== $user->id) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect()->intended(route('portal.dashboard'))
            ->with('status', __('messages.email_verified_success'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('portal.dashboard'));
        }

        $email = strtolower((string) $user->email);

        if (! $this->otp->issue($email, EmailOtp::PURPOSE_REGISTER, $user->full_name)) {
            $seconds = $this->otp->secondsUntilResend($email, EmailOtp::PURPOSE_REGISTER);

            return back()->withErrors([
                'email' => __('messages.otp_throttled', ['seconds' => $seconds]),
            ]);
        }

        return back()->with('status', __('messages.otp_sent_generic'));
    }

    /**
     * Submit a registration OTP while authenticated on the notice page.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('portal.dashboard'));
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $result = $this->otp->verify(
            strtolower((string) $user->email),
            EmailOtp::PURPOSE_REGISTER,
            $data['code']
        );

        if ($result !== 'ok') {
            $message = $result === 'expired'
                ? __('messages.otp_expired')
                : __('messages.otp_invalid');

            return back()->withErrors(['code' => $message]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect()->intended(route('portal.dashboard'))
            ->with('status', __('messages.email_verified_success'));
    }
}
