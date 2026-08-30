<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_is_public(): void
    {
        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('skill.md', false);
    }

    public function test_skill_md_is_public(): void
    {
        $this->get('/skill.md')
            ->assertOk()
            ->assertSee('# CV Skill', false)
            ->assertSee('X-Agent-Client', false);
    }

    public function test_openapi_json_parses(): void
    {
        $response = $this->getJson('/openapi.json');

        $response->assertOk();
        $this->assertSame('3.1.0', $response->json('openapi'));
        $this->assertNotEmpty($response->json('paths./cvs'));
        $this->assertNotEmpty($response->json('paths./cvs/ats-check'));
    }

    public function test_well_known_mcp_json_lists_endpoints(): void
    {
        $this->getJson('/.well-known/mcp.json')
            ->assertOk()
            ->assertJsonPath('mcpServers.cv.url', url('/mcp/cv'))
            ->assertJsonPath('mcpServers.cv-auth.url', url('/mcp/cv/auth'));
    }
}
