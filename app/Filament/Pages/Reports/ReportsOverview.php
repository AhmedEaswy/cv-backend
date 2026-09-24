<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ReportsOverview extends ReportPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports.overview';

    public static function getNavigationLabel(): string
    {
        return __('Overview');
    }

    public function getTitle(): string
    {
        return __('Reports overview');
    }

    public function getStats(): array
    {
        return $this->reports()->overview($this->from ?: null, $this->until ?: null);
    }
}
