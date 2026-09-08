<?php

namespace Tests\Feature\Api;

use App\Models\AtsCheck;
use App\Models\CoverLetter;
use App\Models\Profile;
use App\Models\PublicProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalStatsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['active' => true]);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken];
    }

    public function test_authenticated_user_can_fetch_portal_stats(): void
    {
        Profile::create(['user_id' => $this->user->id, 'name' => 'CV A', 'language' => 'en']);
        Profile::create(['user_id' => $this->user->id, 'name' => 'CV B', 'language' => 'en']);
        CoverLetter::create([
            'user_id' => $this->user->id,
            'name' => 'Letter 1',
            'language' => 'en',
            'content' => 'Hello',
        ]);

        $profile = PublicProfile::create([
            'user_id' => $this->user->id,
            'slug' => 'jane-doe',
            'is_public' => true,
            'views_count' => 12,
            'language' => 'en',
        ]);

        $cv = Profile::where('user_id', $this->user->id)->first();
        AtsCheck::create([
            'user_id' => $this->user->id,
            'profile_id' => $cv->id,
            'source' => 'portal',
            'score' => 72,
            'grade' => 'B',
            'language' => 'en',
            'has_job_description' => false,
            'created_at' => now(),
        ]);
        AtsCheck::create([
            'user_id' => $this->user->id,
            'profile_id' => $cv->id,
            'source' => 'portal',
            'score' => 88,
            'grade' => 'A',
            'language' => 'en',
            'has_job_description' => false,
            'created_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/portal/stats');

        $response->assertOk()
            ->assertJsonPath('result.cvs_count', 2)
            ->assertJsonPath('result.cover_letters_count', 1)
            ->assertJsonPath('result.top_ats_score', 88)
            ->assertJsonPath('result.views_count', 12)
            ->assertJsonPath('result.has_public_profile', true);

        $this->assertNotNull($profile->id);
    }

    public function test_unauthenticated_user_cannot_fetch_portal_stats(): void
    {
        $this->getJson('/api/v1/portal/stats')->assertUnauthorized();
    }
}
