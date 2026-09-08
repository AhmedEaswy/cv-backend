<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserAiSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_and_read_ai_settings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $save = $this->putJson('/api/v1/ai-settings', [
            'provider' => 'openrouter',
            'api_key' => 'sk-test-secret',
            'model' => 'anthropic/claude-3.5-sonnet',
        ]);

        $save->assertOk()
            ->assertJsonPath('result.provider', 'openrouter')
            ->assertJsonPath('result.model', 'anthropic/claude-3.5-sonnet')
            ->assertJsonPath('result.has_api_key', true)
            ->assertJsonMissingPath('result.api_key');

        $this->assertDatabaseHas('user_ai_settings', [
            'user_id' => $user->id,
            'provider' => 'openrouter',
            'base_url' => 'https://openrouter.ai/api/v1',
        ]);

        $stored = UserAiSetting::query()->where('user_id', $user->id)->first();
        $this->assertSame('sk-test-secret', $stored->api_key);

        $show = $this->getJson('/api/v1/ai-settings');
        $show->assertOk()
            ->assertJsonPath('result.has_api_key', true)
            ->assertJsonMissingPath('result.api_key');
    }
}
