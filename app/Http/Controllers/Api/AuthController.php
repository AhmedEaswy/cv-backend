<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends BaseApiController
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], __('messages.register_success'), 201);
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

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse(__('messages.invalid_credentials'), 401);
        }

        // Check if user is active
        if (!$user->isActive()) {
            return $this->errorResponse(__('messages.account_inactive'), 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
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
        return $this->successResponse([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'first_name' => $request->user()->first_name,
                'last_name' => $request->user()->last_name,
                'phone' => $request->user()->phone,
                'active' => $request->user()->active,
                'created_at' => $request->user()->created_at,
            ],
        ], __('messages.me_success'));
    }

    /**
     * Send password reset email
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, __('messages.reset_link_sent'));
        }

        return $this->errorResponse(__('messages.reset_link_failed'), 500);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, __('messages.password_reset_success'));
        }

        return $this->errorResponse(__('messages.invalid_token'), 400);
    }

    /**
     * Verify reset token
     */
    public function verifyResetToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            );
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 404);
        }

        // Use Password facade to verify token
        $tokenExists = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenExists) {
            return $this->errorResponse(__('messages.invalid_token'), 400);
        }

        // Check if token is expired (older than configured expiration time)
        $expiresAt = config('auth.passwords.users.expire', 60);
        if ($tokenExists->created_at && now()->diffInMinutes($tokenExists->created_at) > $expiresAt) {
            return $this->errorResponse(__('messages.reset_token_expired'), 400);
        }

        // Laravel stores tokens hashed, so we need to check using Hash::check
        // The token in DB is already hashed
        if (!Hash::check($request->token, $tokenExists->token)) {
            return $this->errorResponse(__('messages.reset_token_invalid'), 400);
        }

        return $this->successResponse(null, __('messages.reset_token_valid'));
    }
}
