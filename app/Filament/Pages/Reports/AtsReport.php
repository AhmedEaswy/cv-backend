<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AtsReport extends ReportPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.reports.ats-report';

    public static function getNavigationLabel(): string
    {
        return __('ATS report');
    }

    public function getTitle(): string
    {
        return __('ATS report');
    }

    public function getSummary(): array
    {
        return $this->reports()->atsSummary($this->from ?: null, $this->until ?: null);
    }
}
