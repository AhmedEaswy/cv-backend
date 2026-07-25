<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * App Store vs Play Store click distribution, plus a 14-day daily trend
 * so the admin can see whether interest is growing.
 */
class AppClicksChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('dashboard.charts.app_clicks_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('dashboard.charts.app_clicks_description');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $start = Carbon::now()->subDays(13)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $rows = AnalyticsEvent::query()
            ->whereIn('action_type', ['click_app_store', 'click_play_store'])
            ->whereBetween('created_at', [$start, $end])
            ->select('action_type', DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('action_type', 'day')
            ->get();

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[$row->day][$row->action_type] = (int) $row->total;
        }

        $labels = [];
        $appStore = [];
        $playStore = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            $appStore[] = (int) ($byDay[$key]['click_app_store'] ?? 0);
            $playStore[] = (int) ($byDay[$key]['click_play_store'] ?? 0);
            $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.charts.app_store_dataset'),
                    'data' => $appStore,
                ],
                [
                    'label' => __('dashboard.charts.play_store_dataset'),
                    'data' => $playStore,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
