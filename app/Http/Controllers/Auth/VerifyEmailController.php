<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthEventService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function __construct(private readonly AuthEventService $authEvents)
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

        $cooldown = RateLimiter::availableIn($this->authEvents->verificationThrottleKey($user));

        return view('auth.verify-email', [
            'resendCooldown' => $cooldown,
        ]);
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => __('messages.verification_link_invalid')]);
        }

        $user = User::find($id);

        if (! $user || ! hash_equals(sha1((string) $user->email), (string) $hash)) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => __('messages.verification_link_invalid')]);
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

        if (! $this->authEvents->sendVerificationEmail($user)) {
            $seconds = RateLimiter::availableIn($this->authEvents->verificationThrottleKey($user));

            return back()->withErrors([
                'email' => __('messages.verification_throttled', ['seconds' => $seconds]),
            ]);
        }

        return back()->with('status', __('messages.verification_link_resent'));
    }
}
