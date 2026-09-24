<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class GuestInstalls extends ReportPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.reports.guest-installs';

    public static function getNavigationLabel(): string
    {
        return __('Guest installs');
    }

    public function getTitle(): string
    {
        return __('Guest installs');
    }

    public function getInstalls()
    {
        return $this->reports()->guestInstalls($this->from ?: null, $this->until ?: null);
    }

    public function getByDay()
    {
        return $this->reports()->guestInstallsByDay($this->from ?: null, $this->until ?: null);
    }
}
