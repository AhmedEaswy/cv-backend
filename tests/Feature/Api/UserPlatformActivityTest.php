<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserPlatformActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_api_request_updates_last_used_and_platforms(): void
    {
        $user = User::factory()->create(['active' => true]);
        Sanctum::actingAs($user);

        $this->withHeaders([
            'X-App-Platform' => 'web',
        ])->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('result.user.uses_both_platforms', false)
            ->assertJsonPath('result.user.last_app_platform', 'web');

        $user->refresh();
        $this->assertNotNull($user->last_used_at);
        $this->assertSame(['web'], $user->used_platforms);
        $this->assertFalse($user->uses_both_platforms);

        $this->withHeaders([
            'X-App-Platform' => 'android',
        ])->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('result.user.uses_both_platforms', true);

        $user->refresh();
        $this->assertEqualsCanonicalizing(['web', 'android'], $user->used_platforms);
        $this->assertTrue($user->uses_both_platforms);
        $this->assertSame('android', $user->last_app_platform);
    }
}
