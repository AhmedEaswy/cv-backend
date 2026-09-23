<?php

namespace Tests\Feature\Api;

use App\Mail\EmailOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\Auth\EmailOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_user_can_register_without_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('result.verification_required', true)
            ->assertJsonPath('result.email', 'test@example.com')
            ->assertJsonMissingPath('result.token');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNull($user->email_verified_at);

        Mail::assertQueued(EmailOtpMail::class);
        $this->assertDatabaseHas('email_otps', [
            'email' => 'test@example.com',
            'purpose' => EmailOtp::PURPOSE_REGISTER,
        ]);
    }

    public function test_registration_requires_name(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_verify_email_with_otp(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $code = $this->peekOtp('test@example.com', EmailOtp::PURPOSE_REGISTER);

        $response = $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'test@example.com',
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'result' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertNotNull(User::where('email', 'test@example.com')->first()->email_verified_at);
        $this->assertDatabaseMissing('email_otps', [
            'email' => 'test@example.com',
            'purpose' => EmailOtp::PURPOSE_REGISTER,
        ]);
    }

    public function test_verify_email_rejects_wrong_code(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $response = $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'test@example.com',
            'code' => '000000',
        ]);

        $response->assertStatus(400);
        $this->assertNull(User::where('email', 'test@example.com')->first()->email_verified_at);
    }

    public function test_verify_email_rejects_expired_code(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        EmailOtp::query()
            ->where('email', 'test@example.com')
            ->update(['expires_at' => now()->subMinute()]);

        $response = $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'test@example.com',
            'code' => '123456',
        ]);

        $response->assertStatus(400);
    }

    public function test_login_requires_verified_email(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        // Clear grandfathered verification from factory edge cases.
        $user->forceFill(['email_verified_at' => null])->save();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('result.verification_required', true)
            ->assertJsonPath('result.email', 'unverified@example.com');
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'result' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $this->assertFalse($response->json('success'));
    }

    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'password123',
            'active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_login_fails_for_non_existent_user(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_me(): void
    {
        $user = User::factory()->create(['active' => true]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'result' => [
                    'user' => ['id', 'name', 'email', 'active'],
                ],
            ]);

        $this->assertEquals($user->id, $response->json('result.user.id'));
    }

    public function test_unauthenticated_user_cannot_get_me(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['active' => true]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_registration_with_duplicate_email_fails(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_forgot_password_returns_generic_success_for_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nobody@example.com',
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);
        Mail::assertNothingQueued();
    }

    public function test_forgot_password_sends_otp_for_existing_user(): void
    {
        User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'reset@example.com',
        ]);

        $response->assertStatus(200);
        Mail::assertQueued(EmailOtpMail::class);
        $this->assertDatabaseHas('email_otps', [
            'email' => 'reset@example.com',
            'purpose' => EmailOtp::PURPOSE_PASSWORD_RESET,
        ]);
    }

    public function test_user_can_reset_password_with_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'reset@example.com',
        ])->assertStatus(200);

        $code = $this->peekOtp('reset@example.com', EmailOtp::PURPOSE_PASSWORD_RESET);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@example.com',
            'code' => $code,
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword1', $user->password));
    }

    public function test_reset_password_fails_without_valid_otp(): void
    {
        User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@example.com',
            'code' => '123456',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $response->assertStatus(400);
    }

    public function test_resend_verification_is_throttled(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $response = $this->postJson('/api/v1/auth/resend-verification', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_validation_errors_are_localized_via_accept_language(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/v1/auth/login', [
                'password' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'حقل البريد الإلكتروني مطلوب.');
    }

    public function test_validation_general_message_is_localized(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/v1/auth/login', [
                'password' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'البيانات المقدمة غير صالحة.');
    }

    /**
     * Rebuild a plaintext OTP for assertions by replacing the stored hash
     * with a known code (mail is faked, so the real code is not observable).
     */
    protected function peekOtp(string $email, string $purpose): string
    {
        $code = '424242';

        EmailOtp::query()->updateOrCreate(
            ['email' => strtolower($email), 'purpose' => $purpose],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'sent_at' => now(),
            ]
        );

        RateLimiter::clear(app(EmailOtpService::class)->throttleKey($email, $purpose));

        return $code;
    }
}
