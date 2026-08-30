<?php

namespace Tests\Unit;

use App\Services\Agent\AgentDetector;
use Illuminate\Http\Request;
use Tests\TestCase;

class AgentDetectorTest extends TestCase
{
    public function test_mcp_path_is_agent_channel(): void
    {
        $request = Request::create('/mcp/cv', 'POST', [], [], [], [
            'HTTP_X_AGENT_CLIENT' => 'cursor',
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => 'check_ats'],
        ]));

        $detected = (new AgentDetector)->detect($request);

        $this->assertTrue($detected['is_agent']);
        $this->assertSame('mcp', $detected['channel']);
        $this->assertSame('cursor', $detected['agent_client']);
        $this->assertSame('check_ats', $detected['tool_name']);
    }

    public function test_skill_header_marks_rest_as_skill(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'HTTP_X_AGENT_CLIENT' => 'chatgpt',
        ]);

        $detected = (new AgentDetector)->detect($request);

        $this->assertTrue($detected['is_agent']);
        $this->assertSame('skill', $detected['channel']);
        $this->assertSame('chatgpt', $detected['agent_client']);
        $this->assertSame('create_cv', $detected['tool_name']);
    }

    public function test_human_api_call_is_not_agent(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST');

        $detected = (new AgentDetector)->detect($request);

        $this->assertFalse($detected['is_agent']);
        $this->assertSame('api', $detected['channel']);
    }
}
