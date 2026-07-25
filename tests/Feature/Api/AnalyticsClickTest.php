<?php

namespace Tests\Feature\Api;

use App\Models\AnalyticsEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_store_click_creates_event(): void
    {
        $response = $this->postJson('/api/v1/analytics/click', [
            'target' => 'app_store',
            'page' => 'landing',
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'action_type' => 'click_app_store',
            'endpoint' => 'api/v1/analytics/click',
            'method' => 'POST',
            'response_status' => 200,
        ]);

        $event = AnalyticsEvent::where('action_type', 'click_app_store')->first();
        $this->assertNotNull($event);
        $this->assertSame('app_store', $event->request_data['target']);
        $this->assertSame('landing', $event->request_data['page']);
    }

    public function test_play_store_click_creates_event(): void
    {
        $this->postJson('/api/v1/analytics/click', [
            'target' => 'play_store',
        ])->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'action_type' => 'click_play_store',
        ]);
    }

    public function test_invalid_target_is_rejected(): void
    {
        $this->postJson('/api/v1/analytics/click', [
            'target' => 'steam',
        ])->assertStatus(422);

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_missing_target_is_rejected(): void
    {
        $this->postJson('/api/v1/analytics/click', [])
            ->assertStatus(422);

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_click_does_not_require_authentication(): void
    {
        // Visitor is anonymous; the endpoint should not redirect or 401.
        $this->postJson('/api/v1/analytics/click', [
            'target' => 'app_store',
        ])->assertNoContent();
    }
}
