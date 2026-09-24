<?php

namespace App\Http\Middleware;

use App\Jobs\RecordAnalyticsEvent;
use App\Services\Agent\AgentDetector;
use App\Services\AnonymousUserService;
use App\Services\TrackingService;
use App\Services\UserActivityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsMiddleware
{
    public function __construct(
        private TrackingService $trackingService,
        private AgentDetector $agentDetector,
        private AnonymousUserService $anonymousUserService,
        private UserActivityService $userActivityService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $tracking = $this->trackingService->capture($request);
        $user = $request->user() ?? $request->user('sanctum');

        if ($user) {
            try {
                $this->userActivityService->touch($user, $tracking['app_platform'] ?? null);
                $user->refresh();
            } catch (\Throwable) {
                //
            }
        }

        $response = $next($request);

        $duration = (int) round((microtime(true) - $startTime) * 1000);

        $agent = $this->agentDetector->detect($request);

        $endpoint = $request->path();
        $method = $request->method();
        $actionType = $this->resolveActionType($method, $endpoint);
        $meta = $this->buildMeta($request);
        $anonymousUser = $this->anonymousUserService->resolve($request);

        try {
            RecordAnalyticsEvent::dispatch(array_merge($tracking, $agent, [
                'endpoint' => $endpoint,
                'method' => $method,
                'user_agent' => $request->header('User-Agent'),
                'user_id' => $user?->id,
                'anonymous_user_id' => $anonymousUser?->id,
                'profile_id' => $this->resolveProfileId($request, $endpoint),
                'action_type' => $actionType,
                'request_data' => $this->sanitizeRequestData($request),
                'meta' => $meta,
                'response_status' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'created_at' => now(),
            ]));
        } catch (\Throwable) {
            // Never fail the request because analytics could not be recorded.
        }

        return $response;
    }

    private function resolveActionType(string $method, string $endpoint): ?string
    {
        if (str_contains($endpoint, '/mcp/')) {
            return 'mcp_call';
        }

        if (str_contains($endpoint, '/agent-tokens')) {
            return match ($method) {
                'GET' => 'list_agent_tokens',
                'POST' => 'create_agent_token',
                'DELETE' => 'revoke_agent_token',
                default => null,
            };
        }

        if (str_contains($endpoint, '/cvs/import/linkedin')) {
            return 'import_linkedin_cv';
        }

        if (str_contains($endpoint, '/cvs')) {
            return match ($method) {
                'GET' => str_contains($endpoint, '/cvs/') ? 'show_cv' : 'list_cvs',
                'POST' => match (true) {
                    str_contains($endpoint, 'ats-check') => 'ats_check',
                    str_contains($endpoint, 'print') => 'print_cv',
                    str_contains($endpoint, 'duplicate') => 'duplicate_cv',
                    default => 'create_cv',
                },
                'PUT' => 'update_cv',
                'DELETE' => 'delete_cv',
                default => null,
            };
        }

        if (str_contains($endpoint, '/cover-letters')) {
            return match ($method) {
                'GET' => str_contains($endpoint, 'templates')
                    ? 'list_cover_letter_templates'
                    : (str_contains($endpoint, '/cover-letters/') ? 'show_cover_letter' : 'list_cover_letters'),
                'POST' => str_contains($endpoint, 'print') ? 'print_cover_letter' : 'create_cover_letter',
                'PUT' => 'update_cover_letter',
                'DELETE' => 'delete_cover_letter',
                default => null,
            };
        }

        if (str_contains($endpoint, '/public-profiles')) {
            return match ($method) {
                'GET' => str_contains($endpoint, 'templates') ? 'list_public_profile_templates' : 'show_public_profile',
                'POST' => 'create_public_profile',
                'PUT' => 'update_public_profile',
                'DELETE' => 'delete_public_profile',
                default => null,
            };
        }

        if (str_contains($endpoint, '/portal/stats')) {
            return 'portal_stats';
        }

        if (str_contains($endpoint, '/auth')) {
            return match ($method) {
                'POST' => match (true) {
                    str_contains($endpoint, 'login') => 'login',
                    str_contains($endpoint, 'register') => 'register',
                    str_contains($endpoint, 'google') => 'auth_google',
                    str_contains($endpoint, 'linkedin') => 'auth_linkedin',
                    str_contains($endpoint, 'apple') => 'auth_apple',
                    str_contains($endpoint, 'logout') => 'logout',
                    default => 'auth_action',
                },
                'GET' => 'get_user',
                default => null,
            };
        }

        return null;
    }

    private function resolveProfileId(Request $request, string $endpoint): ?int
    {
        if (str_contains($endpoint, '/cvs')) {
            $id = $request->route('id') ?? $request->input('profile_id');

            return $id !== null ? (int) $id : null;
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildMeta(Request $request): ?array
    {
        $meta = [];

        foreach (['template_id', 'client_ref', 'language', 'cover_letter_template_id', 'profile_id', 'cover_letter_id'] as $key) {
            if ($request->filled($key)) {
                $meta[$key] = $request->input($key);
            }
        }

        if ($request->filled('page')) {
            $meta['page'] = $request->input('page');
        }

        return $meta === [] ? null : $meta;
    }

    private function sanitizeRequestData(Request $request): ?array
    {
        $exclude = [
            'password',
            'password_confirmation',
            'token',
            'authorization',
            'code',
            'access_token',
            'refresh_token',
            'id_token',
        ];

        $data = $request->except($exclude);

        if (isset($data['user_data']) && is_array($data['user_data'])) {
            unset($data['user_data']['photo']);
            // Keep structure but drop very large string fields beyond a soft limit.
            foreach ($data['user_data'] as $key => $value) {
                if (is_string($value) && strlen($value) > 2000) {
                    $data['user_data'][$key] = substr($value, 0, 2000).'…';
                }
            }
        }

        foreach ($request->allFiles() as $key => $file) {
            unset($data[$key]);
            if (is_array($file)) {
                $data[$key] = array_map(
                    fn ($f) => $f ? [
                        'name' => $f->getClientOriginalName(),
                        'mime' => $f->getClientMimeType(),
                        'size' => $f->getSize(),
                    ] : null,
                    $file
                );
            } elseif ($file) {
                $data[$key] = [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        return $data ?: null;
    }
}
