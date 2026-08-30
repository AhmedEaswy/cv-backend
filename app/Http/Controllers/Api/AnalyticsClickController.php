<?php

namespace App\Http\Controllers\Api;

use App\Jobs\RecordAnalyticsEvent;
use App\Services\Agent\AgentDetector;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsClickController extends BaseApiController
{
    public function __construct(
        private TrackingService $trackingService,
        private AgentDetector $agentDetector,
    ) {}

    /**
     * Record a click on a landing-page action (app store badges, Connect your AI).
     */
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_]*$/'],
            'page' => ['nullable', 'string', 'max:255'],
        ]);

        $tracking = $this->trackingService->capture($request);
        $agent = $this->agentDetector->detect($request);
        $actionType = 'click_'.$data['target'];

        try {
            RecordAnalyticsEvent::dispatch(array_merge($tracking, $agent, [
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->header('User-Agent'),
                'user_id' => $request->user()?->id,
                'action_type' => $actionType,
                'request_data' => [
                    'target' => $data['target'],
                    'page' => $data['page'] ?? null,
                ],
                'response_status' => 200,
                'duration_ms' => 0,
                'created_at' => now(),
            ]));
        } catch (\Throwable) {
            //
        }

        return response()->noContent();
    }
}
