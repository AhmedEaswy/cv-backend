<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Support\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class LinkedInWebAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.linkedin-openid.client_id' => 'li-client',
            'services.linkedin-openid.client_secret' => 'li-secret',
            'services.linkedin-openid.redirect' => 'http://localhost/auth/linkedin/callback',
            'app.frontend_url' => 'http://frontend.test',
        ]);

        Template::create([
            'name' => 'modern-professional',
            'preview' => 'modern-professional.png',
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    public function test_linkedin_redirect_requires_client_id(): void
    {
        config(['services.linkedin-openid.client_id' => null]);

        $this->get('/auth/linkedin/redirect')
            ->assertRedirect('http://frontend.test/auth/login?error=social');
    }

    public function test_linkedin_callback_creates_user_cv_and_handoff_token(): void
    {
        $socialUser = (new SocialiteUser)->setRaw([
            'sub' => 'li-1',
            'given_name' => 'Ada',
            'family_name' => 'Lovelace',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])->map([
            'id' => 'li-1',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);
        $socialUser->token = 'linkedin-access-token';

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('linkedin-openid')->andReturn($provider);

        Http::fake([
            'https://api.linkedin.com/v2/userinfo' => Http::response([
                'given_name' => 'Ada',
                'family_name' => 'Lovelace',
                'email' => 'ada@example.com',
            ]),
            'https://api.linkedin.com/v2/me*' => Http::response([
                'localizedHeadline' => 'Engineer',
            ]),
        ]);

        $response = $this->withSession([
            'url.intended' => 'http://frontend.test/portal',
            'linkedin.intent' => 'login',
        ])->get('/auth/linkedin/callback');

        $response->assertOk();
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
        $this->assertDatabaseHas('social_accounts', [
            'provider_name' => SocialProvider::LINKEDIN,
            'provider_id' => 'li-1',
        ]);
        $this->assertDatabaseCount('profiles', 1);
        $this->assertStringContainsString('cv.auth.token', $response->getContent());
        $this->assertTrue(
            str_contains($response->getContent(), '/portal/cvs/')
            || str_contains($response->getContent(), '\/portal\/cvs\/')
        );
    }
}
