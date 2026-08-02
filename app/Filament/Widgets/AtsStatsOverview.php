<?php

namespace App\Filament\Widgets;

use App\Models\AtsCheck;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AtsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $total = AtsCheck::count();
        $last30 = AtsCheck::where('created_at', '>=', $thirtyDaysAgo)->count();
        $avgScore = (int) round((float) AtsCheck::avg('score'));
        $avgScore30 = (int) round((float) AtsCheck::where('created_at', '>=', $thirtyDaysAgo)->avg('score'));
        $withJd = AtsCheck::where('has_job_description', true)->count();
        $passing = AtsCheck::where('score', '>=', 70)->count();
        $passingRate = $total > 0 ? (int) round(($passing / $total) * 100) : 0;

        return [
            Stat::make(__('dashboard.stats.ats_checks_total'), number_format($total))
                ->description(__('dashboard.stats.new_in_30_days', ['count' => $last30]))
                ->descriptionIcon('heroicon-m-shield-check')
                ->icon(Heroicon::ShieldCheck)
                ->color('primary'),

            Stat::make(__('dashboard.stats.ats_avg_score'), $avgScore.'%')
                ->description(__('dashboard.stats.ats_avg_score_30d', ['score' => $avgScore30]))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->icon(Heroicon::ChartBar)
                ->color('info'),

            Stat::make(__('dashboard.stats.ats_pass_rate'), $passingRate.'%')
                ->description(__('dashboard.stats.ats_pass_rate_hint'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->icon(Heroicon::CheckBadge)
                ->color('success'),

            Stat::make(__('dashboard.stats.ats_with_jd'), number_format($withJd))
                ->description(__('dashboard.stats.ats_with_jd_hint'))
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->icon(Heroicon::DocumentMagnifyingGlass)
                ->color('warning'),
        ];
    }
}
