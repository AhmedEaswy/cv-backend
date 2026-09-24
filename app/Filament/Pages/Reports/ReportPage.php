<?php

namespace App\Filament\Pages\Reports;

use App\Services\Reports\ReportQueryService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

abstract class ReportPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public string $from = '';

    public string $until = '';

    public function mount(): void
    {
        $this->from = now()->subDays(30)->toDateString();
        $this->until = now()->toDateString();
    }

    public function updatedFrom(): void
    {
        // Livewire re-renders getters.
    }

    public function updatedUntil(): void
    {
        //
    }

    protected function reports(): ReportQueryService
    {
        return app(ReportQueryService::class);
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Reports');
    }
}
