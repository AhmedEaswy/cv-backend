<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CoverLetter;
use App\Models\Profile;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Headline numbers for the admin dashboard.
 *
 * Pulls live counts from the application tables and from the analytics
 * events recorded for the landing page App Store / Play Store clicks.
 *
 * All user-visible strings are translated via __() so the widget follows
 * the admin's current locale.
 */
class AppStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $totalUsers = User::count();
        $newUsersLast30d = User::where('created_at', '>=', $thirtyDaysAgo)->count();

        $totalCvs = Profile::count();
        $newCvsLast30d = Profile::where('created_at', '>=', $thirtyDaysAgo)->count();

        $totalCoverLetters = CoverLetter::count();
        $newCoverLettersLast30d = CoverLetter::where('created_at', '>=', $thirtyDaysAgo)->count();

        $appStoreClicks = AnalyticsEvent::where('action_type', 'click_app_store')->count();
        $playStoreClicks = AnalyticsEvent::where('action_type', 'click_play_store')->count();
        $totalAppClicks = $appStoreClicks + $playStoreClicks;

        $appClicksLast30d = AnalyticsEvent::whereIn('action_type', ['click_app_store', 'click_play_store'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        return [
            Stat::make(__('dashboard.stats.users_total'), number_format($totalUsers))
                ->description(__('dashboard.stats.new_in_30_days', ['count' => $newUsersLast30d]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon(Heroicon::Users)
                ->color('primary'),

            Stat::make(__('dashboard.stats.cvs_created'), number_format($totalCvs))
                ->description(__('dashboard.stats.new_in_30_days', ['count' => $newCvsLast30d]))
                ->descriptionIcon('heroicon-m-document-text')
                ->icon(Heroicon::DocumentText)
                ->color('info'),

            Stat::make(__('dashboard.stats.cover_letters_created'), number_format($totalCoverLetters))
                ->description(__('dashboard.stats.new_in_30_days', ['count' => $newCoverLettersLast30d]))
                ->descriptionIcon('heroicon-m-envelope')
                ->icon(Heroicon::Envelope)
                ->color('warning'),

            Stat::make(__('dashboard.stats.app_downloads'), number_format($totalAppClicks))
                ->description(__('dashboard.stats.app_downloads_summary', [
                    'total' => $appClicksLast30d,
                    'ios' => $appStoreClicks,
                    'android' => $playStoreClicks,
                ]))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success'),
        ];
    }
}
