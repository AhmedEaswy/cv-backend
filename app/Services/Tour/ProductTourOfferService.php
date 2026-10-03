<?php

namespace App\Services\Tour;

use App\Enums\UserTourProgressStatus;
use App\Models\ProductTour;
use App\Models\User;
use App\Models\UserTourProgress;

class ProductTourOfferService
{
    /**
     * @return array{tours: list<array<string, mixed>>}
     */
    public function offerForUser(User $user): array
    {
        $tours = ProductTour::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->with(['steps' => fn ($q) => $q->where('is_enabled', true)->orderBy('sort_order')])
            ->get();

        $progressByTour = UserTourProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('product_tour_id', $tours->pluck('id'))
            ->get()
            ->keyBy('product_tour_id');

        $locale = app()->getLocale();
        $offered = [];

        foreach ($tours as $tour) {
            $progress = $progressByTour->get($tour->id);
            if ($progress !== null) {
                continue;
            }

            if ($tour->steps->isEmpty()) {
                continue;
            }

            $offered[] = [
                'key' => $tour->key,
                'steps' => $tour->steps->map(fn ($step) => [
                    'id' => $step->id,
                    'sort_order' => $step->sort_order,
                    'title' => $step->translation('title', $locale),
                    'body' => $step->translation('body', $locale),
                ])->values()->all(),
            ];
        }

        return ['tours' => $offered];
    }

    public function recordProgress(User $user, string $tourKey, UserTourProgressStatus $status): ?UserTourProgress
    {
        $tour = ProductTour::query()->where('key', $tourKey)->first();
        if (! $tour) {
            return null;
        }

        return UserTourProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'product_tour_id' => $tour->id,
            ],
            [
                'status' => $status,
                'completed_at' => now(),
            ],
        );
    }

    public function resetProgress(User $user, string $tourKey): bool
    {
        $tour = ProductTour::query()->where('key', $tourKey)->first();
        if (! $tour) {
            return false;
        }

        UserTourProgress::query()
            ->where('user_id', $user->id)
            ->where('product_tour_id', $tour->id)
            ->delete();

        return true;
    }
}
