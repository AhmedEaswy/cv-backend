<?php

namespace App\Http\Controllers\Api;

use App\Services\Portal\PortalStatsService;
use Illuminate\Http\Request;

class PortalStatsController extends BaseApiController
{
    public function __invoke(Request $request, PortalStatsService $stats)
    {
        return $this->successResponse(
            $stats->forUser($request->user()),
            __('messages.portal_stats_retrieved')
        );
    }
}
