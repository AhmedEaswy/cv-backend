<?php

namespace Tests\Feature;

use App\Support\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AppleWebAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.apple.client_id' => 'com.example.service',
            'services.apple.client_secret' => 'apple-secret',
            'services.apple.redirect' => 'http://localhost/auth/apple/callback',
            'app.frontend_url' => 'http://frontend.test',
        ]);
    }

    public function test_apple_redirect_requires_client_id(): void
    {
        config(['services.apple.client_id' => null]);

        $this->get('/auth/apple/redirect')
            ->assertRedirect('http://frontend.test/auth/login?error=social');
    }

    public function test_apple_callback_creates_user_and_handoff_token(): void
    {
        $socialUser = (new SocialiteUser)->setRaw([
            'sub' => 'apple-1',
            'email' => 'ada@example.com',
            'name' => [
                'firstName' => 'Ada',
                'lastName' => 'Lovelace',
            ],
        ])->map([
            'id' => 'apple-1',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);
        $socialUser->token = 'apple-id-token';

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('cookieNonce')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('apple')->andReturn($provider);

        $response = $this->withSession([
            'url.intended' => 'http://frontend.test/portal',
        ])->post('/auth/apple/callback');

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $this->assertDatabaseHas('social_accounts', [
            'provider_name' => SocialProvider::APPLE,
            'provider_id' => 'apple-1',
        ]);
        $this->assertStringContainsString('cv.auth.token', $response->getContent());
        $this->assertTrue(
            str_contains($response->getContent(), '/portal')
            || str_contains($response->getContent(), '\/portal')
        );
    }

    public function test_apple_get_callback_also_works(): void
    {
        $socialUser = (new SocialiteUser)->setRaw([
            'sub' => 'apple-2',
            'email' => 'alan@example.com',
        ])->map([
            'id' => 'apple-2',
            'name' => null,
            'email' => 'alan@example.com',
        ]);
        $socialUser->token = 'apple-id-token';

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('cookieNonce')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('apple')->andReturn($provider);

        $this->get('/auth/apple/callback')->assertOk();
        $this->assertDatabaseHas('users', ['email' => 'alan@example.com']);
    }
}
