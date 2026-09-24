<?php

namespace App\Services\Reports;

use App\Models\AnalyticsEvent;
use App\Models\AnonymousUser;
use App\Models\AtsCheck;
use App\Models\CoverLetter;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportQueryService
{
    public function range(?string $from, ?string $until): array
    {
        $start = $from
            ? Carbon::parse($from)->startOfDay()
            : now()->subDays(30)->startOfDay();
        $end = $until
            ? Carbon::parse($until)->endOfDay()
            : now()->endOfDay();

        return [$start, $end];
    }

    public function overview(?string $from, ?string $until): array
    {
        [$start, $end] = $this->range($from, $until);

        $events = AnalyticsEvent::query()->whereBetween('created_at', [$start, $end]);

        return [
            'users' => User::query()->whereBetween('created_at', [$start, $end])->count(),
            'guest_installs' => AnonymousUser::query()->whereBetween('first_seen_at', [$start, $end])->count(),
            'users_both_platforms' => User::query()->where('uses_both_platforms', true)->count(),
            'active_users' => User::query()->whereBetween('last_used_at', [$start, $end])->count(),
            'cvs' => Profile::query()->whereBetween('created_at', [$start, $end])->count(),
            'cover_letters' => CoverLetter::query()->whereBetween('created_at', [$start, $end])->count(),
            'prints' => (clone $events)->whereIn('action_type', ['print_cv', 'print_cover_letter'])->count(),
            'ats_checks' => AtsCheck::query()->whereBetween('created_at', [$start, $end])->count(),
            'store_clicks' => (clone $events)->whereIn('action_type', ['click_app_store', 'click_play_store'])->count(),
        ];
    }

    public function funnelByDay(?string $from, ?string $until, ?string $platform = null): Collection
    {
        [$start, $end] = $this->range($from, $until);

        $actions = ['register', 'create_cv', 'print_cv', 'create_cover_letter'];

        $query = AnalyticsEvent::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('action_type', $actions)
            ->select(
                DB::raw('DATE(created_at) as day'),
                'action_type',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('day', 'action_type')
            ->orderBy('day');

        if ($platform) {
            $query->where('app_platform', $platform);
        }

        return $query->get();
    }

    public function funnelTotals(?string $from, ?string $until, ?string $platform = null): array
    {
        [$start, $end] = $this->range($from, $until);

        $query = AnalyticsEvent::query()
            ->whereBetween('created_at', [$start, $end]);

        if ($platform) {
            $query->where('app_platform', $platform);
        }

        $counts = [];
        foreach (['register', 'create_cv', 'print_cv', 'create_cover_letter', 'login'] as $action) {
            $counts[$action] = (clone $query)->where('action_type', $action)->count();
        }

        return $counts;
    }

    public function deviceBreakdown(?string $from, ?string $until, string $groupBy = 'os'): Collection
    {
        [$start, $end] = $this->range($from, $until);

        $column = match ($groupBy) {
            'os_version' => 'os_version',
            'device_model' => 'device_model',
            'app_platform' => 'app_platform',
            'device_type' => 'device_type',
            default => 'os',
        };

        return AnalyticsEvent::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull($column)
            ->select($column.' as label', DB::raw('COUNT(*) as total'))
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(20)
            ->get();
    }

    public function guestInstalls(?string $from, ?string $until): Collection
    {
        [$start, $end] = $this->range($from, $until);

        return AnonymousUser::query()
            ->withCount(['profiles', 'coverLetters'])
            ->whereBetween('first_seen_at', [$start, $end])
            ->orderByDesc('first_seen_at')
            ->limit(100)
            ->get();
    }

    public function guestInstallsByDay(?string $from, ?string $until): Collection
    {
        [$start, $end] = $this->range($from, $until);

        return AnonymousUser::query()
            ->whereBetween('first_seen_at', [$start, $end])
            ->select(DB::raw('DATE(first_seen_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    public function atsSummary(?string $from, ?string $until): array
    {
        [$start, $end] = $this->range($from, $until);

        $base = AtsCheck::query()->whereBetween('created_at', [$start, $end]);
        $total = (clone $base)->count();
        $passing = (clone $base)->where('score', '>=', 70)->count();

        return [
            'total' => $total,
            'avg_score' => $total > 0 ? (int) round((float) (clone $base)->avg('score')) : 0,
            'pass_rate' => $total > 0 ? (int) round(($passing / $total) * 100) : 0,
            'with_jd' => (clone $base)->where('has_job_description', true)->count(),
            'by_language' => (clone $base)
                ->select('language', DB::raw('COUNT(*) as total'), DB::raw('AVG(score) as avg_score'))
                ->groupBy('language')
                ->orderByDesc('total')
                ->get(),
            'by_grade' => (clone $base)
                ->select('grade', DB::raw('COUNT(*) as total'))
                ->groupBy('grade')
                ->orderByDesc('total')
                ->get(),
        ];
    }
}
