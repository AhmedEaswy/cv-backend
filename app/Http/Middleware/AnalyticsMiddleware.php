<?php

namespace App\Http\Middleware;

use App\Models\AnalyticsEvent;
use App\Services\TrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsMiddleware
{
    public function __construct(
        private TrackingService $trackingService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (int) round((microtime(true) - $startTime) * 1000);

        $tracking = $this->trackingService->capture($request);

        $endpoint = $request->path();
        $method = $request->method();
        $actionType = $this->resolveActionType($method, $endpoint);

        AnalyticsEvent::create(array_merge($tracking, [
            'endpoint' => $endpoint,
            'method' => $method,
            'user_agent' => $request->header('User-Agent'),
            'user_id' => $request->user()?->id,
            'profile_id' => $this->resolveProfileId($request, $endpoint),
            'action_type' => $actionType,
            'request_data' => $this->sanitizeRequestData($request),
            'response_status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'created_at' => now(),
        ]));

        return $response;
    }

    private function resolveActionType(string $method, string $endpoint): ?string
    {
        if (str_contains($endpoint, '/cvs')) {
            return match ($method) {
                'GET' => 'list_cvs',
                'POST' => str_contains($endpoint, 'print') ? 'print_cv' : 'create_cv',
                'PUT' => 'update_cv',
                'DELETE' => 'delete_cv',
                default => null,
            };
        }

        if (str_contains($endpoint, '/cover-letters')) {
            return match ($method) {
                'GET' => 'list_cover_letters',
                'POST' => str_contains($endpoint, 'print') ? 'print_cover_letter' : 'create_cover_letter',
                'PUT' => 'update_cover_letter',
                'DELETE' => 'delete_cover_letter',
                default => null,
            };
        }

        if (str_contains($endpoint, '/auth')) {
            return match ($method) {
                'POST' => str_contains($endpoint, 'login') ? 'login' : (str_contains($endpoint, 'register') ? 'register' : 'auth_action'),
                'GET' => 'get_user',
                default => null,
            };
        }

        return null;
    }

    private function resolveProfileId(Request $request, string $endpoint): ?int
    {
        if (str_contains($endpoint, '/cvs')) {
            return $request->route('profile') ?? $request->route('id') ?? null;
        }

        if (str_contains($endpoint, '/cover-letters')) {
            return null;
        }

        return null;
    }

    private function sanitizeRequestData(Request $request): ?array
    {
        $exclude = ['password', 'password_confirmation', 'token', 'authorization'];

        $data = $request->except($exclude);

        return $data ?: null;
    }
}
