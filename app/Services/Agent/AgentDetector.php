<?php

namespace App\Services\Agent;

use Illuminate\Http\Request;

class AgentDetector
{
    /**
     * @var list<string>
     */
    private const AGENT_USER_AGENTS = [
        'ChatGPT-User',
        'ChatGPT-UserBot',
        'OAI-SearchBot',
        'ClaudeBot',
        'Claude-User',
        'Claude-SearchBot',
        'PerplexityBot',
        'Perplexity-User',
        'Google-Extended',
        'Google-CloudVertexBot',
        'Gemini-Deep-Research',
        'Copilot',
        'BingPreview',
        'MiniMaxAI',
        'Grok',
        'Cursor',
    ];

    /**
     * @return array{channel: string, is_agent: bool, agent_name: ?string, agent_client: ?string, tool_name: ?string}
     */
    public function detect(Request $request): array
    {
        $clientHeader = $this->normalizeClient(
            $request->header('X-Agent-Client')
            ?? $request->header('X-MCP-Client')
        );
        $userAgent = (string) $request->userAgent();
        $uaMatch = $this->matchUserAgent($userAgent);

        if ($request->is('mcp/*')) {
            $payload = $request->json()->all();
            $method = is_array($payload) ? ($payload['method'] ?? null) : null;
            $toolName = null;

            if ($method === 'tools/call' && is_array($payload['params'] ?? null)) {
                $toolName = $payload['params']['name'] ?? null;
            }

            return [
                'channel' => 'mcp',
                'is_agent' => true,
                'agent_name' => 'mcp',
                'agent_client' => $clientHeader ?? $uaMatch,
                'tool_name' => is_string($toolName) ? $toolName : (is_string($method) ? $method : null),
            ];
        }

        if ($clientHeader) {
            return [
                'channel' => 'skill',
                'is_agent' => true,
                'agent_name' => $clientHeader,
                'agent_client' => $clientHeader,
                'tool_name' => $this->inferToolFromPath($request),
            ];
        }

        if ($uaMatch) {
            return [
                'channel' => 'skill',
                'is_agent' => true,
                'agent_name' => $uaMatch,
                'agent_client' => $uaMatch,
                'tool_name' => $this->inferToolFromPath($request),
            ];
        }

        $channel = $request->is('api/*') ? 'api' : 'web';

        return [
            'channel' => $channel,
            'is_agent' => false,
            'agent_name' => null,
            'agent_client' => null,
            'tool_name' => null,
        ];
    }

    private function normalizeClient(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        if ($value === '' || strlen($value) > 64) {
            return null;
        }

        return preg_replace('/[^a-z0-9_-]/', '', $value) ?: null;
    }

    private function matchUserAgent(string $userAgent): ?string
    {
        foreach (self::AGENT_USER_AGENTS as $needle) {
            if (stripos($userAgent, $needle) !== false) {
                return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $needle) ?? $needle);
            }
        }

        return null;
    }

    private function inferToolFromPath(Request $request): ?string
    {
        $path = $request->path();
        $method = $request->method();

        return match (true) {
            str_contains($path, 'ats-check') => 'check_ats',
            str_contains($path, 'cvs/print') => 'render_cv_pdf',
            str_contains($path, 'cover-letters/print') => 'render_cover_letter_pdf',
            str_contains($path, '/cvs') && $method === 'POST' => 'create_cv',
            str_contains($path, '/cover-letters') && $method === 'POST' => 'create_cover_letter',
            default => null,
        };
    }
}
