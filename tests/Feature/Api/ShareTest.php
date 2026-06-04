<?php

namespace Tests\Feature\Api;

use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_active_templates(): void
    {
        Template::create([
            'name' => 'Modern Professional',
            'preview' => 'modern.png',
            'is_active' => true,
            'is_default' => true,
        ]);

        Template::create([
            'name' => 'Office Manager',
            'preview' => 'office.png',
            'is_active' => true,
            'is_default' => false,
        ]);

        // Create an inactive template (should not appear)
        Template::create([
            'name' => 'Inactive Template',
            'preview' => 'inactive.png',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/shares/templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'result' => [
                    '*' => ['id', 'name', 'preview', 'description', 'created_at', 'updated_at'],
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(2, $response->json('result'));
    }

    public function test_templates_endpoint_returns_empty_when_no_active_templates(): void
    {
        $response = $this->getJson('/api/v1/shares/templates');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('result'));
    }

    public function test_templates_endpoint_is_public(): void
    {
        $response = $this->getJson('/api/v1/shares/templates');

        $response->assertStatus(200);
    }

    public function test_templates_response_has_correct_format(): void
    {
        $template = Template::create([
            'name' => 'Test Template',
            'preview' => 'test.png',
            'description' => 'A test template',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/shares/templates');

        $response->assertStatus(200);

        $result = $response->json('result')[0];
        $this->assertEquals($template->id, $result['id']);
        $this->assertEquals('Test Template', $result['name']);
        $this->assertEquals('A test template', $result['description']);
    }
}
