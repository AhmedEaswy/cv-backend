<?php

namespace App\Http\Controllers\Api;

use App\Models\AnalyticsEvent;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsClickController extends BaseApiController
{
    public function __construct(
        private TrackingService $trackingService,
    ) {
    }

    /**
     * Record a click on a landing-page action (e.g. App Store / Play Store badge).
     *
     * Public, no auth — the visitor is anonymous. Designed to be hit
     * via navigator.sendBeacon() so the request survives the page
     * navigating away to the app store.
     */
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'target' => ['required', 'string', Rule::in(['app_store', 'play_store'])],
            'page' => ['nullable', 'string', 'max:255'],
        ]);

        $tracking = $this->trackingService->capture($request);
        $actionType = 'click_' . $data['target'];

        AnalyticsEvent::create(array_merge($tracking, [
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

        // 204 No Content — no body. Beacon endpoints should return quickly
        // and never carry a JSON payload.
        return response()->noContent();
    }
}
