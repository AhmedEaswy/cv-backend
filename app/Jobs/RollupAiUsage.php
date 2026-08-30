<?php

namespace App\Jobs;

use App\Models\AiUsageDaily;
use App\Models\AnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RollupAiUsage implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?string $date = null) {}

    public function handle(): void
    {
        $day = $this->date
            ? Carbon::parse($this->date)->startOfDay()
            : Carbon::yesterday()->startOfDay();
        $end = $day->copy()->endOfDay();

        $rows = AnalyticsEvent::query()
            ->where('is_agent', true)
            ->whereBetween('created_at', [$day, $end])
            ->select([
                DB::raw('DATE(created_at) as day'),
                'channel',
                'agent_client',
                'tool_name',
                DB::raw('COUNT(*) as calls'),
                DB::raw('COUNT(DISTINCT ip_address) as unique_ips'),
            ])
            ->groupBy('day', 'channel', 'agent_client', 'tool_name')
            ->get();

        foreach ($rows as $row) {
            AiUsageDaily::query()->updateOrCreate(
                [
                    'date' => $row->day,
                    'channel' => $row->channel ?? 'api',
                    'agent_client' => $row->agent_client ?: '',
                    'tool_name' => $row->tool_name ?: '',
                ],
                [
                    'calls' => (int) $row->calls,
                    'unique_ips' => (int) $row->unique_ips,
                ],
            );
        }
    }
}
