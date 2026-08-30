<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Uri('cv://resources/api-catalog')]
#[MimeType('application/json')]
#[Description('OpenAPI 3.1 catalog of the public REST API at /api/v1.')]
class ApiCatalogResource extends Resource
{
    public function handle(Request $request): Response
    {
        $path = resource_path('agent/openapi.json');

        if (! is_file($path)) {
            return Response::error('OpenAPI catalog is not published yet.');
        }

        return Response::text((string) file_get_contents($path));
    }
}
