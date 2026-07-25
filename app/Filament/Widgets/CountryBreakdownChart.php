<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

/**
 * Top countries by analytics events. Local/private IPs are
 * geo-looked-up to null by TrackingService, so they show up as
 * "Unknown" and are excluded from the top-N chart.
 */
class CountryBreakdownChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'pie';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('dashboard.charts.countries_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('dashboard.charts.countries_description');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $rows = AnalyticsEvent::query()
            ->whereNotNull('country')
            ->select('country', DB::raw('COUNT(*) as total'))
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $labels = $rows->pluck('country')->all();
        $values = $rows->pluck('total')->map(fn ($v) => (int) $v)->all();

        // Fall back to a single translated "No data" slice so the chart
        // still renders cleanly during early deployment.
        if (empty($labels)) {
            $labels = [__('dashboard.charts.no_data')];
            $values = [1];
        }

        $palette = ['#5c17e7', '#a78bfa', '#d97a4a', '#130e21', '#22c55e', '#0ea5e9', '#f59e0b', '#ef4444'];
        $colors = array_slice($palette, 0, count($labels));

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.charts.countries_dataset'),
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
