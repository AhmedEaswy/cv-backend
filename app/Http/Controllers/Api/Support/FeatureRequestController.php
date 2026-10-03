<?php

namespace App\Http\Controllers\Api\Support;

use App\Enums\FeatureRequestStatus;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\FeatureRequest;
use App\Models\FeatureRequestVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeatureRequestController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = FeatureRequest::query()
            ->published()
            ->orderByDesc('vote_count')
            ->orderByDesc('created_at');

        $userId = $request->user()?->id;

        return $this->paginatedOrAll(
            $query,
            $request,
            fn (FeatureRequest $item) => $this->formatRequest($item, $userId),
            __('messages.feature_requests_retrieved'),
            20,
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'body' => ['required', 'string', 'min:10', 'max:8000'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), 422, $validator->errors());
        }

        $featureRequest = FeatureRequest::create([
            'user_id' => $request->user()->id,
            'title' => $validator->validated()['title'],
            'body' => $validator->validated()['body'],
            'status' => FeatureRequestStatus::UnderReview,
            'is_published' => false,
            'vote_count' => 0,
        ]);

        return $this->successResponse(
            $this->formatRequest($featureRequest, $request->user()->id, true),
            __('messages.feature_request_created'),
            201,
        );
    }

    public function vote(Request $request, int $id)
    {
        $featureRequest = FeatureRequest::query()->published()->find($id);
        if (! $featureRequest) {
            return $this->errorResponse(__('messages.feature_request_not_found'), 404);
        }

        $userId = $request->user()->id;

        $existing = FeatureRequestVote::query()
            ->where('feature_request_id', $featureRequest->id)
            ->where('user_id', $userId)
            ->exists();

        if ($existing) {
            return $this->errorResponse(__('messages.feature_request_already_voted'), 409);
        }

        DB::transaction(function () use ($featureRequest, $userId) {
            FeatureRequestVote::create([
                'feature_request_id' => $featureRequest->id,
                'user_id' => $userId,
            ]);
            $featureRequest->increment('vote_count');
        });

        $featureRequest->refresh();

        return $this->successResponse(
            $this->formatRequest($featureRequest, $userId),
            __('messages.feature_request_voted'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRequest(FeatureRequest $item, ?int $userId, bool $includeBody = false): array
    {
        $voted = false;
        if ($userId !== null) {
            $voted = $item->votes()->where('user_id', $userId)->exists();
        }

        $data = [
            'id' => $item->id,
            'title' => $item->title,
            'status' => $item->status->value,
            'vote_count' => $item->vote_count,
            'has_voted' => $voted,
            'created_at' => $item->created_at?->toIso8601String(),
        ];

        if ($includeBody || $userId === $item->user_id) {
            $data['body'] = $item->body;
        }

        return $data;
    }
}
