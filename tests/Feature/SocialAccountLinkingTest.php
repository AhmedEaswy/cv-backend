<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Auth\SocialLinkFlow;
use App\Support\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAccountLinkingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'google-client',
            'services.google.client_secret' => 'google-secret',
            'app.frontend_url' => 'http://frontend.test',
        ]);
    }

    public function test_index_lists_providers_with_link_state(): void
    {
        $user = User::factory()->create();
        $this->linkAccount($user, SocialProvider::GOOGLE, 'g-1');
        Sanctum::actingAs($user);

        $providers = collect($this->getJson('/api/v1/auth/social-accounts')
            ->assertOk()
            ->json('result.providers'))
            ->keyBy('provider');

        $this->assertTrue($providers['google']['linked']);
        $this->assertTrue($providers['google']['can_unlink']);
        $this->assertFalse($providers['linkedin']['linked']);
    }

    public function test_link_returns_redirect_url_with_token(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $url = $this->postJson('/api/v1/auth/social-accounts/google/link')
            ->assertOk()
            ->json('result.url');

        $this->assertStringContainsString('/auth/google/redirect?link_token=', $url);
    }

    public function test_link_rejects_already_linked_provider(): void
    {
        $user = User::factory()->create();
        $this->linkAccount($user, SocialProvider::GOOGLE, 'g-1');
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/social-accounts/google/link')->assertStatus(409);
    }

    public function test_callback_links_provider_to_signed_in_user(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $token = app(SocialLinkFlow::class)->issueToken($user);
        $this->mockGoogle('g-new', 'different@example.com');

        $this->withCookie('social_link', $token)
            ->get('/auth/google/callback')
            ->assertRedirect('http://frontend.test/portal/settings?linked=google');

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider_name' => SocialProvider::GOOGLE,
            'provider_id' => 'g-new',
        ]);
        $this->assertDatabaseMissing('users', ['email' => 'different@example.com']);
    }

    public function test_callback_refuses_account_owned_by_another_user(): void
    {
        $owner = User::factory()->create();
        $this->linkAccount($owner, SocialProvider::GOOGLE, 'g-taken');
        $user = User::factory()->create();
        $token = app(SocialLinkFlow::class)->issueToken($user);
        $this->mockGoogle('g-taken', 'someone@example.com');

        $this->withCookie('social_link', $token)
            ->get('/auth/google/callback')
            ->assertRedirect('http://frontend.test/portal/settings?link_error=taken&provider=google');

        $this->assertSame(0, $user->socialAccounts()->count());
    }

    public function test_unlink_removes_provider(): void
    {
        $user = User::factory()->create();
        $this->linkAccount($user, SocialProvider::GOOGLE, 'g-1');
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/auth/social-accounts/google')->assertOk();

        $this->assertSame(0, $user->socialAccounts()->count());
    }

    public function test_unlink_blocks_last_method_for_placeholder_email(): void
    {
        $user = User::factory()->create(['email' => 'apple+123@users.local']);
        $this->linkAccount($user, SocialProvider::APPLE, '123');
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/auth/social-accounts/apple')->assertStatus(422);

        $this->assertSame(1, $user->socialAccounts()->count());
    }

    private function linkAccount(User $user, string $provider, string $providerId): void
    {
        SocialAccount::create([
            'user_id' => $user->id,
            'provider_name' => $provider,
            'provider_id' => $providerId,
        ]);
    }

    private function mockGoogle(string $id, string $email): void
    {
        $socialUser = (new SocialiteUser)->setRaw(['sub' => $id, 'email' => $email])
            ->map(['id' => $id, 'name' => 'Test User', 'email' => $email]);
        $socialUser->token = 'google-token';

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
