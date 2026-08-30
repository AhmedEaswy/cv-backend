<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class AiHumanRatioChart extends ChartWidget
{
    protected static ?int $sort = 24;

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('ai_analytics.charts.human_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('ai_analytics.charts.human_description');
    }

    protected function getData(): array
    {
        $agent = AnalyticsEvent::query()->where('is_agent', true)->count();
        $human = AnalyticsEvent::query()->where('is_agent', false)->count();

        return [
            'datasets' => [
                [
                    'label' => __('ai_analytics.charts.human_heading'),
                    'data' => [$agent, $human],
                    'backgroundColor' => ['#5c17e7', '#c8c4bf'],
                ],
            ],
            'labels' => [
                __('ai_analytics.charts.human_agent'),
                __('ai_analytics.charts.human_human'),
            ],
        ];
    }
}
