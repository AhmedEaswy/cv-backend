<?php

namespace App\Services\Auth;

use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Centralized helpers for email verification OTP dispatch and
 * the rate limits around them.
 */
class AuthEventService
{
    public const VERIFICATION_THROTTLE_SECONDS = 60;

    public const RESET_THROTTLE_SECONDS = 60;

    public function __construct(private readonly EmailOtpService $otp)
    {
    }

    /**
     * Send a fresh verification OTP to the user.
     */
    public function sendVerificationEmail(User $user): bool
    {
        if (! $user instanceof MustVerifyEmail) {
            return false;
        }

        return $this->otp->issue(
            strtolower((string) $user->email),
            EmailOtp::PURPOSE_REGISTER,
            $user->full_name
        );
    }

    /**
     * Reset OTP throttle. Returns false when the user must wait.
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
        return $this->otp->throttleKey(strtolower((string) $user->email), EmailOtp::PURPOSE_REGISTER);
    }

    public function resetThrottleKey(string $email): string
    {
        return $this->otp->throttleKey(strtolower($email), EmailOtp::PURPOSE_PASSWORD_RESET);
    }
}
