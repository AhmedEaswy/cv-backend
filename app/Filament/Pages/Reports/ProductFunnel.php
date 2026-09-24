<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ProductFunnel extends ReportPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFunnel;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reports.product-funnel';

    public ?string $platform = null;

    public static function getNavigationLabel(): string
    {
        return __('Product funnel');
    }

    public function getTitle(): string
    {
        return __('Product funnel');
    }

    public function getTotals(): array
    {
        return $this->reports()->funnelTotals($this->from ?: null, $this->until ?: null, $this->platform ?: null);
    }

    public function getDailyRows(): array
    {
        $rows = $this->reports()->funnelByDay($this->from ?: null, $this->until ?: null, $this->platform ?: null);
        $byDay = [];

        foreach ($rows as $row) {
            $day = $row->day;
            $byDay[$day] ??= [
                'day' => $day,
                'register' => 0,
                'create_cv' => 0,
                'print_cv' => 0,
                'create_cover_letter' => 0,
            ];
            $byDay[$day][$row->action_type] = (int) $row->total;
        }

        return array_values($byDay);
    }
}
