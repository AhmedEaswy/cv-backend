<?php

namespace App\Http\Controllers\Api\Portal;

use App\Enums\UserTourProgressStatus;
use App\Http\Controllers\Api\BaseApiController;
use App\Services\Tour\ProductTourOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductTourController extends BaseApiController
{
    public function __construct(private readonly ProductTourOfferService $tours)
    {
    }

    public function offer(Request $request)
    {
        return $this->successResponse(
            $this->tours->offerForUser($request->user()),
            __('messages.product_tour_offer_retrieved'),
        );
    }

    public function complete(Request $request, string $key)
    {
        return $this->record($request, $key, UserTourProgressStatus::Completed);
    }

    public function dismiss(Request $request, string $key)
    {
        return $this->record($request, $key, UserTourProgressStatus::Dismissed);
    }

    private function record(Request $request, string $key, UserTourProgressStatus $status)
    {
        $validator = Validator::make(['key' => $key], [
            'key' => ['required', 'string', 'max:64'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), 422, $validator->errors());
        }

        $progress = $this->tours->recordProgress($request->user(), $key, $status);
        if (! $progress) {
            return $this->errorResponse(__('messages.product_tour_not_found'), 404);
        }

        return $this->successResponse([
            'key' => $key,
            'status' => $progress->status->value,
            'completed_at' => $progress->completed_at?->toIso8601String(),
        ], __('messages.product_tour_progress_saved'));
    }
}
