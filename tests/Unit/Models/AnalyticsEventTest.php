<?php

namespace Tests\Unit\Models;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_event_can_be_created(): void
    {
        $user = User::factory()->create();

        $event = AnalyticsEvent::create([
            'endpoint' => 'api/v1/cvs',
            'method' => 'POST',
            'ip_address' => '127.0.0.1',
            'user_id' => $user->id,
            'action_type' => 'create_cv',
            'response_status' => 201,
            'duration_ms' => 42,
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('analytics_events', ['endpoint' => 'api/v1/cvs']);
        $this->assertEquals('create_cv', $event->action_type);
    }

    public function test_analytics_event_user_relation(): void
    {
        $user = User::factory()->create();

        $event = AnalyticsEvent::create([
            'endpoint' => 'api/v1/cvs',
            'method' => 'GET',
            'user_id' => $user->id,
            'action_type' => 'list_cvs',
            'response_status' => 200,
            'duration_ms' => 15,
            'created_at' => now(),
        ]);

        $this->assertEquals($user->id, $event->user->id);
    }
}
