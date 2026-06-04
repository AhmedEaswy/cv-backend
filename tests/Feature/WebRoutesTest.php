<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    public function test_homepage_returns_200(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_test_locale_endpoint_returns_json(): void
    {
        $response = $this->getJson('/test-locale');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_locale',
                'direction',
                'welcome_message',
            ]);
    }

    public function test_api_returns_404_for_unknown_routes(): void
    {
        $response = $this->getJson('/api/v1/nonexistent');

        $response->assertStatus(404);
    }
}
