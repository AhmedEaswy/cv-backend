<?php

namespace Tests\Feature\Api;

use App\Models\SocialAccount;
use App\Models\Template;
use App\Models\User;
use App\Support\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class LinkedInAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Template::create([
            'name' => 'modern-professional',
            'preview' => 'modern-professional.png',
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    public function test_linkedin_token_creates_user_and_cv(): void
    {
        $this->mockLinkedInSocialite();
        $this->fakeLinkedInApis();

        $response = $this->postJson('/api/v1/auth/linkedin', [
            'code' => 'linkedin-access-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('result.user.email', 'ada@example.com')
            ->assertJsonPath('result.cv.user_data.firstName', 'Ada')
            ->assertJsonPath('result.cv.user_data.jobTitle', 'Software Engineer');

        $this->assertNotEmpty($response->json('result.token'));
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
        $this->assertDatabaseHas('social_accounts', [
            'provider_name' => SocialProvider::LINKEDIN,
            'provider_id' => 'li-1',
        ]);
        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_linkedin_token_skips_cv_when_import_cv_is_false(): void
    {
        $this->mockLinkedInSocialite();
        $this->fakeLinkedInApis();

        $response = $this->postJson('/api/v1/auth/linkedin', [
            'code' => 'linkedin-access-token',
            'import_cv' => false,
        ]);

        $response->assertOk()->assertJsonMissingPath('result.cv');
        $this->assertDatabaseCount('profiles', 0);
    }

    public function test_authenticated_user_can_import_cv_from_stored_linkedin_token(): void
    {
        $this->fakeLinkedInApis();

        $user = User::factory()->create(['active' => true]);
        SocialAccount::create([
            'user_id' => $user->id,
            'provider_name' => SocialProvider::LINKEDIN,
            'provider_id' => 'li-1',
            'provider_token' => 'stored-linkedin-token',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cvs/import/linkedin');

        $response->assertCreated()
            ->assertJsonPath('result.user_data.firstName', 'Ada');
        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_linkedin_import_requires_connected_account(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cvs/import/linkedin')
            ->assertStatus(409);
    }

    public function test_linkedin_token_auth_fails_when_socialite_rejects_token(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('userFromToken')->andThrow(new \RuntimeException('bad token'));

        Socialite::shouldReceive('driver')->with('linkedin-openid')->andReturn($provider);

        $this->postJson('/api/v1/auth/linkedin', [
            'code' => 'bad',
        ])->assertStatus(401);
    }

    private function mockLinkedInSocialite(): void
    {
        $socialUser = (new SocialiteUser)->setRaw([
            'sub' => 'li-1',
            'name' => 'Ada Lovelace',
            'given_name' => 'Ada',
            'family_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'picture' => 'https://media.licdn.test/ada.png',
        ])->map([
            'id' => 'li-1',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'avatar' => 'https://media.licdn.test/ada.png',
        ]);
        $socialUser->token = 'linkedin-access-token';

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('userFromToken')->andReturn($socialUser);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('linkedin-openid')->andReturn($provider);
    }

    private function fakeLinkedInApis(): void
    {
        Http::fake([
            'https://api.linkedin.com/v2/userinfo' => Http::response([
                'sub' => 'li-1',
                'name' => 'Ada Lovelace',
                'given_name' => 'Ada',
                'family_name' => 'Lovelace',
                'email' => 'ada@example.com',
                'locale' => 'en_US',
            ]),
            'https://api.linkedin.com/v2/me*' => Http::response([
                'localizedHeadline' => 'Software Engineer',
                'vanityName' => 'ada-lovelace',
            ]),
        ]);
    }
}
