<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AgentDiscoveryController extends Controller
{
    public function llms(): Response
    {
        return $this->textFile('llms.txt', 'text/plain; charset=UTF-8');
    }

    public function skill(): Response
    {
        return $this->textFile('skill.md', 'text/markdown; charset=UTF-8');
    }

    public function openapi(): JsonResponse
    {
        $path = resource_path('agent/openapi.json');

        abort_unless(is_file($path), 404);

        $decoded = json_decode((string) file_get_contents($path), true);

        abort_unless(is_array($decoded), 500, 'OpenAPI catalog is invalid JSON.');

        return response()->json($decoded);
    }

    public function mcp(): JsonResponse
    {
        $base = rtrim((string) config('app.url'), '/');

        return response()->json([
            'mcpServers' => [
                'cv' => [
                    'url' => $base.'/mcp/cv',
                    'description' => 'Public CV tools: templates, ATS check, create CV / cover letter, render PDF.',
                ],
                'cv-auth' => [
                    'url' => $base.'/mcp/cv/auth',
                    'description' => 'Same tools plus saved-account CRUD. Send Authorization: Bearer <agent-token>.',
                ],
            ],
            'skill' => $base.'/skill.md',
            'openapi' => $base.'/openapi.json',
            'llms' => $base.'/llms.txt',
        ]);
    }

    private function textFile(string $name, string $contentType): Response
    {
        $path = resource_path('agent/'.$name);

        abort_unless(is_file($path), 404);

        return response((string) file_get_contents($path), SymfonyResponse::HTTP_OK, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
