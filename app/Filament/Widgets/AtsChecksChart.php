<?php

namespace App\Filament\Widgets;

use App\Models\AtsCheck;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class AtsChecksChart extends ChartWidget
{
    protected static ?int $sort = 8;

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('dashboard.charts.ats_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('dashboard.charts.ats_description');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $counts = AtsCheck::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('total', 'day');

        $averages = AtsCheck::query()
            ->selectRaw('DATE(created_at) as day, AVG(score) as avg_score')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('avg_score', 'day');

        $labels = [];
        $checkValues = [];
        $avgValues = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            $checkValues[] = (int) ($counts[$key] ?? 0);
            $avgValues[] = isset($averages[$key]) ? (int) round((float) $averages[$key]) : 0;
            $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.charts.ats_checks_dataset'),
                    'data' => $checkValues,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('dashboard.charts.ats_avg_score_dataset'),
                    'data' => $avgValues,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
