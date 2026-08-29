<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Portal\PortalStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly PortalStatsService $stats)
    {
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('portal.dashboard', [
            'user' => $user,
            'stats' => $this->stats->forUser($user),
        ]);
    }
}
