<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\Auth\EmailOtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends BaseApiController
{
    public function __construct(private readonly EmailOtpService $otp)
    {
    }

    /**
     * Register a new user (unverified). An OTP is emailed; no token yet.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $name = trim((string) $request->input('name'));
        $parts = preg_split('/\s+/u', $name, 2) ?: [$name];
        $email = strtolower((string) $request->input('email'));

        $user = User::create([
            'name' => $name,
            'first_name' => $parts[0] ?? $name,
            'last_name' => $parts[1] ?? '',
            'email' => $email,
            'password' => (string) $request->input('password'),
            'type' => UserType::USER->value,
            'active' => true,
        ]);

        // Registered listener calls User::sendEmailVerificationNotification() → OTP.
        event(new Registered($user));

        return $this->successResponse([
            'verification_required' => true,
            'email' => $email,
        ], __('messages.account_created_verify'), 201);
    }

    /**
     * Verify registration OTP and return an auth token.
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $email = strtolower((string) $request->input('email'));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return $this->errorResponse(__('messages.user_not_found'), 404);
        }

        if ($user->hasVerifiedEmail()) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $this->userPayload($user),
                'token' => $token,
            ], __('messages.email_verified_success'));
        }

        $result = $this->otp->verify($email, EmailOtp::PURPOSE_REGISTER, (string) $request->input('code'));

        if ($result !== 'ok') {
            return $this->otpErrorResponse($result);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $this->userPayload($user),
            'token' => $token,
        ], __('messages.email_verified_success'));
    }

    /**
     * Resend the registration verification OTP.
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $email = strtolower((string) $request->input('email'));
        $user = User::query()->where('email', $email)->first();

        // Generic response — never reveal whether the address is on file.
        $generic = $this->successResponse([
            'email' => $email,
        ], __('messages.otp_sent_generic'));

        if (! $user || $user->hasVerifiedEmail()) {
            return $generic;
        }

        if (! $this->otp->issue($email, EmailOtp::PURPOSE_REGISTER, $user->full_name)) {
            $seconds = $this->otp->secondsUntilResend($email, EmailOtp::PURPOSE_REGISTER);

            return $this->errorResponse(
                __('messages.otp_throttled', ['seconds' => $seconds]),
                429,
                ['email' => [__('messages.otp_throttled', ['seconds' => $seconds])]]
            );
        }

        return $generic;
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse(__('messages.invalid_credentials'), 401);
        }

        if (! $user->isActive()) {
            return $this->errorResponse(__('messages.account_inactive'), 403);
        }

        if (! $user->hasVerifiedEmail()) {
            // Soft-nudge: try to send a fresh code (throttled).
            $this->otp->issue(
                strtolower((string) $user->email),
                EmailOtp::PURPOSE_REGISTER,
                $user->full_name
            );

            return response()->json([
                'success' => false,
                'message' => __('messages.email_not_verified'),
                'code' => 403,
                'errors' => null,
                'result' => [
                    'verification_required' => true,
                    'email' => strtolower((string) $user->email),
                ],
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $this->userPayload($user),
            'token' => $token,
        ], __('messages.login_success'));
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, __('messages.logout_success'));
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'active' => $user->active,
                'email_verified_at' => $user->email_verified_at,
                'last_used_at' => $user->last_used_at,
                'last_app_platform' => $user->last_app_platform,
                'used_platforms' => $user->used_platforms ?? [],
                'uses_both_platforms' => (bool) $user->uses_both_platforms,
                'created_at' => $user->created_at,
            ],
        ], __('messages.me_success'));
    }

    /**
     * Send password reset OTP email
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $email = strtolower((string) $request->input('email'));

        if (! $this->otp->issuePasswordReset($email)) {
            // issuePasswordReset always returns true; keep branch for safety.
        }

        return $this->successResponse([
            'email' => $email,
        ], __('messages.reset_code_sent_generic'));
    }

    /**
     * Reset password with email OTP
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $email = strtolower((string) $request->input('email'));
        $result = $this->otp->verify($email, EmailOtp::PURPOSE_PASSWORD_RESET, (string) $request->input('code'));

        if ($result !== 'ok') {
            return $this->otpErrorResponse($result);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return $this->errorResponse(__('messages.user_not_found'), 404);
        }

        $user->forceFill([
            'password' => (string) $request->input('password'),
        ])->save();

        return $this->successResponse(null, __('messages.password_reset_success'));
    }

    /**
     * @return array{id: int, name: string, email: string}
     */
    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    protected function otpErrorResponse(string $result)
    {
        $message = $result === 'expired'
            ? __('messages.otp_expired')
            : __('messages.otp_invalid');

        return $this->errorResponse($message, 400, [
            'code' => [$message],
        ]);
    }
}
