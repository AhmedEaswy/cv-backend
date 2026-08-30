<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AiUsageOverview extends BaseWidget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $since = Carbon::now()->subDays(30);

        $agentCalls = AnalyticsEvent::query()->where('is_agent', true)->count();
        $agentCalls30d = AnalyticsEvent::query()->where('is_agent', true)->where('created_at', '>=', $since)->count();

        $mcpCalls = AnalyticsEvent::query()->where('channel', 'mcp')->count();
        $skillCalls = AnalyticsEvent::query()->where('channel', 'skill')->count();

        $topPlatform = AnalyticsEvent::query()
            ->where('is_agent', true)
            ->whereNotNull('agent_client')
            ->selectRaw('agent_client, COUNT(*) as total')
            ->groupBy('agent_client')
            ->orderByDesc('total')
            ->value('agent_client');

        $skillCopies = AnalyticsEvent::query()
            ->where('action_type', 'click_ai_connect_copy_skill')
            ->count();

        $connectClicks = AnalyticsEvent::query()
            ->where(function ($query) {
                $query->where('action_type', 'like', 'click_ai_connect_%')
                    ->orWhere('action_type', 'click_hero_connect_ai');
            })
            ->count();

        $signups = AnalyticsEvent::query()
            ->where('is_agent', true)
            ->where('action_type', 'register')
            ->count();

        $signedIn = AnalyticsEvent::query()
            ->where('is_agent', true)
            ->whereNotNull('user_id')
            ->count();

        return [
            Stat::make(__('ai_analytics.stats.agent_calls'), number_format($agentCalls))
                ->description(__('ai_analytics.stats.new_in_30_days', ['count' => $agentCalls30d]))
                ->icon(Heroicon::CpuChip)
                ->color('primary'),
            Stat::make(__('ai_analytics.stats.mcp_calls'), number_format($mcpCalls))
                ->description(__('ai_analytics.stats.skill_calls', ['count' => $skillCalls]))
                ->icon(Heroicon::CommandLine)
                ->color('info'),
            Stat::make(__('ai_analytics.stats.top_platform'), $topPlatform ?: '—')
                ->description(__('ai_analytics.stats.top_platform_hint'))
                ->icon(Heroicon::GlobeAlt)
                ->color('warning'),
            Stat::make(__('ai_analytics.stats.skill_copies'), number_format($skillCopies))
                ->description(__('ai_analytics.stats.connect_clicks', ['count' => $connectClicks]))
                ->icon(Heroicon::Clipboard)
                ->color('success'),
            Stat::make(__('ai_analytics.stats.signups'), number_format($signups))
                ->description(__('ai_analytics.stats.signed_in', ['count' => $signedIn]))
                ->icon(Heroicon::Users)
                ->color('gray'),
        ];
    }
}
