<?php

namespace App\Services\Auth;

use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

/**
 * Centralized helpers for email verification, password reset link
 * dispatching, and the rate limits around them.
 *
 * Each helper returns true on success, false when throttled — callers
 * should never silently swallow a throttle; the UI must surface it.
 */
class AuthEventService
{
    public const VERIFICATION_THROTTLE_SECONDS = 60;

    public const RESET_THROTTLE_SECONDS = 60;

    /**
     * Send a fresh verification link to the user. Throttled per-user
     * to prevent abuse (e.g. flooding mailboxes or spending quota).
     */
    public function sendVerificationEmail(User $user): bool
    {
        if (! $user instanceof MustVerifyEmail) {
            return false;
        }

        $key = $this->verificationThrottleKey($user);

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1((string) $user->getEmailForVerification()),
            ]
        );

        Mail::to($user->email)->send(new VerifyEmailMail($user, $signedUrl));

        RateLimiter::hit($key, self::VERIFICATION_THROTTLE_SECONDS);

        return true;
    }

    /**
     * Reset link throttle. Returns false when the user must wait.
     */
    public function canSendResetLink(string $email): bool
    {
        $key = $this->resetThrottleKey($email);

        return ! RateLimiter::tooManyAttempts($key, 1);
    }

    public function markResetLinkSent(string $email): void
    {
        RateLimiter::hit(
            $this->resetThrottleKey($email),
            self::RESET_THROTTLE_SECONDS
        );
    }

    public function verificationThrottleKey(User $user): string
    {
        return 'verify-email:'.$user->getKey();
    }

    public function resetThrottleKey(string $email): string
    {
        return 'password-reset:'.strtolower($email);
    }
}
