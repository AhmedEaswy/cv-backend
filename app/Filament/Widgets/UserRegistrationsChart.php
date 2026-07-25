<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

/**
 * Daily new-user signups for the last 30 days.
 */
class UserRegistrationsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('dashboard.charts.users_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('dashboard.charts.users_description');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $end = Carbon::now()->subDay()->endOfDay();

        $rows = User::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            $values[] = (int) ($rows[$key] ?? 0);
            $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.charts.users_dataset'),
                    'data' => $values,
                    'tension' => 0.35,
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
