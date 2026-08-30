<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class AiToolsChart extends ChartWidget
{
    protected static ?int $sort = 22;

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('ai_analytics.charts.tools_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('ai_analytics.charts.tools_description');
    }

    protected function getData(): array
    {
        $rows = AnalyticsEvent::query()
            ->where('is_agent', true)
            ->whereNotNull('tool_name')
            ->select('tool_name', DB::raw('COUNT(*) as total'))
            ->groupBy('tool_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('ai_analytics.charts.tools_dataset'),
                    'data' => $rows->pluck('total')->map(fn ($n) => (int) $n)->all(),
                ],
            ],
            'labels' => $rows->pluck('tool_name')->all(),
        ];
    }
}
