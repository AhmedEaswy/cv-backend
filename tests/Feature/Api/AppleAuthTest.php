<?php

namespace Tests\Feature\Api;

use App\Support\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AppleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.apple.client_id' => 'com.example.service',
            'services.apple.native_client_id' => 'com.example.app',
        ]);
    }

    public function test_apple_identity_token_creates_user(): void
    {
        $this->mockAppleSocialite();

        $response = $this->postJson('/api/v1/auth/apple', [
            'code' => 'apple-identity-token',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('result.user.email', 'ada@example.com');

        $this->assertNotEmpty($response->json('result.token'));
        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $this->assertDatabaseHas('social_accounts', [
            'provider_name' => SocialProvider::APPLE,
            'provider_id' => 'apple-1',
        ]);
    }

    public function test_apple_token_auth_fails_when_identity_token_is_rejected(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('userByIdentityToken')
            ->andThrow(new \RuntimeException('bad token'));

        Socialite::shouldReceive('driver')->with('apple')->andReturn($provider);

        $this->postJson('/api/v1/auth/apple', [
            'code' => 'bad',
        ])->assertStatus(401);
    }

    public function test_apple_uses_native_client_id_when_configured(): void
    {
        $this->mockAppleSocialite();

        $this->postJson('/api/v1/auth/apple', [
            'code' => 'apple-identity-token',
        ])->assertOk();

        $this->assertSame('com.example.app', config('services.apple.client_id'));
    }

    private function mockAppleSocialite(): void
    {
        $socialUser = (new SocialiteUser)->setRaw([
            'sub' => 'apple-1',
            'email' => 'ada@example.com',
        ])->map([
            'id' => 'apple-1',
            'name' => null,
            'email' => 'ada@example.com',
        ]);
        $socialUser->token = 'apple-identity-token';

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('userByIdentityToken')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('apple')->andReturn($provider);
    }
}
