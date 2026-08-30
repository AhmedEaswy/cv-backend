<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class AiPlatformChart extends ChartWidget
{
    protected static ?int $sort = 23;

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('ai_analytics.charts.platform_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('ai_analytics.charts.platform_description');
    }

    protected function getData(): array
    {
        $rows = AnalyticsEvent::query()
            ->where('is_agent', true)
            ->whereNotNull('agent_client')
            ->select('agent_client', DB::raw('COUNT(*) as total'))
            ->groupBy('agent_client')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('ai_analytics.charts.platform_dataset'),
                    'data' => $rows->pluck('total')->map(fn ($n) => (int) $n)->all(),
                    'backgroundColor' => ['#5c17e7', '#d97a4a', '#1f7a4f', '#323e86', '#b27318', '#4a4a4a', '#c0392b', '#8a8a8a'],
                ],
            ],
            'labels' => $rows->pluck('agent_client')->all(),
        ];
    }
}
