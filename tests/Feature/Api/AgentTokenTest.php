<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Support\AgentAbilities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_token(): void
    {
        $this->postJson('/api/v1/agent-tokens', ['name' => 'cursor'])
            ->assertUnauthorized();
    }

    public function test_user_can_create_list_and_revoke_token(): void
    {
        $user = User::factory()->create(['active' => true]);

        $create = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/agent-tokens', ['name' => 'claude-desktop']);

        $create->assertCreated()
            ->assertJsonPath('success', true);

        $plain = $create->json('result.token');
        $id = $create->json('result.id');

        $this->assertNotEmpty($plain);
        $this->assertStringStartsWith(AgentAbilities::TOKEN_NAME_PREFIX, $create->json('result.name'));

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/agent-tokens')
            ->assertOk()
            ->assertJsonPath('result.0.id', $id)
            ->assertJsonMissingPath('result.0.token');

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/agent-tokens/'.$id)
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/agent-tokens')
            ->assertJsonPath('result', []);
    }
}
