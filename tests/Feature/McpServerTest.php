<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AgentAbilities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_mcp_lists_public_tools(): void
    {
        $response = $this->mcp('tools/list');

        $response->assertOk();
        $names = collect($response->json('result.tools'))->pluck('name')->all();

        $this->assertContains('list-cv-templates', $names);
        $this->assertContains('check-ats', $names);
        $this->assertContains('create-cv', $names);
        $this->assertNotContains('list-my-cvs', $names);
    }

    public function test_authenticated_mcp_lists_account_tools(): void
    {
        $user = User::factory()->create(['active' => true]);
        $token = $user->createToken('agent:cursor', AgentAbilities::all())->plainTextToken;

        $response = $this->mcp('tools/list', [], [
            'Authorization' => 'Bearer '.$token,
        ], '/mcp/cv/auth');

        $response->assertOk();
        $names = collect($response->json('result.tools'))->pluck('name')->all();

        $this->assertContains('list-cv-templates', $names);
        $this->assertContains('list-my-cvs', $names);
        $this->assertContains('get-cv', $names);
        $this->assertContains('delete-cv', $names);
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, string>  $headers
     */
    private function mcp(string $method, array $params = [], array $headers = [], string $url = '/mcp/cv')
    {
        return $this->postJson($url, [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => array_merge([
                '_meta' => [
                    'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
                    'io.modelcontextprotocol/clientCapabilities' => new \stdClass,
                ],
            ], $params),
        ], array_merge([
            'MCP-Protocol-Version' => '2026-07-28',
            'Mcp-Method' => $method,
        ], $headers));
    }
}
