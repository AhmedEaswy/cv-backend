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
                    '*' => ['id', 'name', 'preview', 'description', 'is_default', 'created_at', 'updated_at'],
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

    public function test_templates_endpoint_supports_pagination(): void
    {
        foreach (range(1, 5) as $i) {
            Template::create([
                'name' => "Template {$i}",
                'preview' => "t{$i}.png",
                'is_active' => true,
                'is_default' => $i === 1,
            ]);
        }

        $page1 = $this->getJson('/api/v1/shares/templates?page=1&per_page=2');
        $page1->assertStatus(200)
            ->assertJsonPath('result.meta.current_page', 1)
            ->assertJsonPath('result.meta.per_page', 2)
            ->assertJsonPath('result.meta.total', 5)
            ->assertJsonPath('result.meta.has_more', true);
        $this->assertCount(2, $page1->json('result.data'));

        $page2 = $this->getJson('/api/v1/shares/templates?page=2&per_page=2');
        $page2->assertStatus(200)
            ->assertJsonPath('result.meta.current_page', 2)
            ->assertJsonPath('result.meta.has_more', true);
        $this->assertCount(2, $page2->json('result.data'));

        $page3 = $this->getJson('/api/v1/shares/templates?page=3&per_page=2');
        $page3->assertStatus(200)
            ->assertJsonPath('result.meta.current_page', 3)
            ->assertJsonPath('result.meta.has_more', false);
        $this->assertCount(1, $page3->json('result.data'));
    }
}
