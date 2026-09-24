<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class DevicesAndOs extends ReportPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.reports.devices-and-os';

    public string $groupBy = 'os';

    public static function getNavigationLabel(): string
    {
        return __('Devices & OS');
    }

    public function getTitle(): string
    {
        return __('Devices & OS');
    }

    public function getBreakdown()
    {
        return $this->reports()->deviceBreakdown($this->from ?: null, $this->until ?: null, $this->groupBy);
    }
}
