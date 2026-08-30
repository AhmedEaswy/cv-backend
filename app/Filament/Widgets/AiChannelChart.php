<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AiChannelChart extends ChartWidget
{
    protected static ?int $sort = 21;

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('ai_analytics.charts.channel_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('ai_analytics.charts.channel_description');
    }

    protected function getData(): array
    {
        $start = Carbon::now()->subDays(13)->startOfDay();
        $end = Carbon::now()->endOfDay();
        $channels = ['mcp', 'skill', 'api'];

        $rows = AnalyticsEvent::query()
            ->where('is_agent', true)
            ->whereBetween('created_at', [$start, $end])
            ->select('channel', DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('channel', 'day')
            ->get();

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[$row->day][$row->channel] = (int) $row->total;
        }

        $labels = [];
        $series = [];
        foreach ($channels as $channel) {
            $series[$channel] = [];
        }

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            foreach ($channels as $channel) {
                $series[$channel][] = (int) ($byDay[$key][$channel] ?? 0);
            }
            $cursor->addDay();
        }

        return [
            'datasets' => [
                ['label' => 'MCP', 'data' => $series['mcp']],
                ['label' => 'Skill', 'data' => $series['skill']],
                ['label' => 'API', 'data' => $series['api']],
            ],
            'labels' => $labels,
        ];
    }
}
