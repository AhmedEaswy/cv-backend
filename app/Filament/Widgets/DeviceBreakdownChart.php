<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

/**
 * Mobile vs Desktop split based on the device string captured by
 * TrackingService ("Mobile / iOS / Chrome", "Desktop / Windows / Firefox", ...).
 */
class DeviceBreakdownChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('dashboard.charts.devices_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('dashboard.charts.devices_description');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $rows = AnalyticsEvent::query()
            ->whereNotNull('device')
            ->select('device', DB::raw('COUNT(*) as total'))
            ->groupBy('device')
            ->get();

        $mobile = 0;
        $desktop = 0;
        foreach ($rows as $row) {
            if (str_starts_with($row->device, 'Mobile')) {
                $mobile += (int) $row->total;
            } else {
                $desktop += (int) $row->total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.charts.devices_dataset'),
                    'data' => [$mobile, $desktop],
                    'backgroundColor' => ['#5c17e7', '#d97a4a'],
                ],
            ],
            'labels' => [
                __('dashboard.charts.device_mobile'),
                __('dashboard.charts.device_desktop'),
            ],
        ];
    }
}
