<?php

namespace App\Filament\Widgets;

use App\Models\CoverLetter;
use App\Models\Profile;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

/**
 * CVs and cover letters created per day, last 30 days.
 */
class ContentActivityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('dashboard.charts.content_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('dashboard.charts.content_description');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $end = Carbon::now()->subDay()->endOfDay();

        $cvs = Profile::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('total', 'day');

        $coverLetters = CoverLetter::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $cvValues = [];
        $coverLetterValues = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            $cvValues[] = (int) ($cvs[$key] ?? 0);
            $coverLetterValues[] = (int) ($coverLetters[$key] ?? 0);
            $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.charts.cvs_dataset'),
                    'data' => $cvValues,
                ],
                [
                    'label' => __('dashboard.charts.cover_letters_dataset'),
                    'data' => $coverLetterValues,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
