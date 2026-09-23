<?php

namespace App\Services\Auth;

use App\Mail\EmailOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class EmailOtpService
{
    public const RESEND_THROTTLE_SECONDS = 60;

    /**
     * Issue a fresh OTP for the given email and purpose.
     * Returns false when resend is throttled.
     */
    public function issue(string $email, string $purpose, ?string $userName = null): bool
    {
        $email = strtolower(trim($email));
        $key = $this->throttleKey($email, $purpose);

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }

        $code = $this->generateCode();

        EmailOtp::query()->updateOrCreate(
            ['email' => $email, 'purpose' => $purpose],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(EmailOtp::TTL_MINUTES),
                'sent_at' => now(),
            ]
        );

        try {
            Mail::to($email)->send(new EmailOtpMail($email, $code, $purpose, $userName));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        RateLimiter::hit($key, self::RESEND_THROTTLE_SECONDS);

        return true;
    }

    /**
     * Issue a password-reset OTP only when the account exists.
     * Always returns true so callers can respond generically.
     */
    public function issuePasswordReset(string $email): bool
    {
        $email = strtolower(trim($email));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return true;
        }

        $this->issue($email, EmailOtp::PURPOSE_PASSWORD_RESET, $user->full_name);

        return true;
    }

    /**
     * Verify a submitted code. Consumes the OTP on success.
     *
     * @return 'ok'|'invalid'|'expired'|'throttled'
     */
    public function verify(string $email, string $purpose, string $code): string
    {
        $email = strtolower(trim($email));
        $otp = EmailOtp::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->first();

        if (! $otp) {
            return 'invalid';
        }

        if ($otp->isExpired() || $otp->hasExceededAttempts()) {
            $otp->delete();

            return 'expired';
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            if ($otp->fresh()?->hasExceededAttempts()) {
                $otp->delete();

                return 'expired';
            }

            return 'invalid';
        }

        $otp->delete();

        return 'ok';
    }

    public function secondsUntilResend(string $email, string $purpose): int
    {
        return RateLimiter::availableIn($this->throttleKey(strtolower(trim($email)), $purpose));
    }

    public function throttleKey(string $email, string $purpose): string
    {
        return 'email-otp:'.$purpose.':'.strtolower($email);
    }

    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
