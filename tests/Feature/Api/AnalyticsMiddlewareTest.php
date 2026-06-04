<?php

namespace Tests\Feature\Api;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['active' => true]);
    }

    private function authHeader(): array
    {
        $token = $this->user->createToken('test')->plainTextToken;

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_create_cv_request_logs_analytics_event(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/cvs', [
                'name' => 'Test CV',
                'language' => 'en',
            ]);

        $this->assertDatabaseHas('analytics_events', [
            'endpoint' => 'api/v1/cvs',
            'method' => 'POST',
            'action_type' => 'create_cv',
        ]);
    }

    public function test_list_cvs_logs_analytics_event(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/cvs');

        $this->assertDatabaseHas('analytics_events', [
            'endpoint' => 'api/v1/cvs',
            'method' => 'GET',
            'action_type' => 'list_cvs',
        ]);
    }

    public function test_login_logs_analytics_event(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('analytics_events', [
            'endpoint' => 'api/v1/auth/login',
            'method' => 'POST',
            'action_type' => 'login',
        ]);
    }

    public function test_analytics_event_includes_duration(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/cvs');

        $event = AnalyticsEvent::latest()->first();
        $this->assertNotNull($event->duration_ms);
        $this->assertGreaterThanOrEqual(0, $event->duration_ms);
    }

    public function test_analytics_event_has_response_status(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/cvs', [
                'name' => 'Test',
                'language' => 'en',
            ]);

        $event = AnalyticsEvent::latest()->first();
        $this->assertEquals(201, $event->response_status);
    }
}
