<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SendContactReplyJob;
use App\Models\ContactMessageReply;
use App\Repositories\PublicProfileRepository;
use App\Services\Contact\ContactSpamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicProfileInboxController extends BaseApiController
{
    public function __construct(
        private PublicProfileRepository $repository,
        private ContactSpamService $spamService,
    ) {
    }

    public function index(Request $request)
    {
        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return $this->successResponse([], __('messages.public_profile_not_found'));
        }

        if (! $profile->inboxIsEnabled()) {
            return $this->successResponse([], __('messages.inbox_disabled'));
        }

        $query = $profile->contactMessages()
            ->visible()
            ->with(['replies' => fn ($q) => $q->orderBy('created_at')]);

        if ($request->has('page')) {
            $perPage = min(50, max(1, (int) $request->input('per_page', 20)));

            return $this->successResponse(
                $this->formatPaginator($query->paginate($perPage), fn ($m) => $this->formatMessage($m)),
                __('messages.portal_inbox_title')
            );
        }

        $messages = $query->limit(200)->get()->map(fn ($m) => $this->formatMessage($m))->values();

        return $this->successResponse($messages, __('messages.portal_inbox_title'));
    }

    public function markRead(Request $request, int $id)
    {
        $message = $this->findOwnedMessage($request, $id);
        if (! $message) {
            return $this->errorResponse(__('messages.contact_message_not_found'), 404);
        }

        $message->markAsRead();

        return $this->successResponse(
            $this->formatMessage($message->fresh(['replies'])),
            __('messages.contact_message_marked_read')
        );
    }

    public function reportSpam(Request $request, int $id)
    {
        $message = $this->findOwnedMessage($request, $id);
        if (! $message) {
            return $this->errorResponse(__('messages.contact_message_not_found'), 404);
        }

        DB::transaction(function () use ($message, $request) {
            $message->forceFill([
                'is_spam' => true,
                'hidden_at' => now(),
            ])->save();

            $this->spamService->block(
                $message->email,
                $message->ip_address,
                'reported',
                $message->id,
                $request->user()->id,
            );
        });

        return $this->successResponse(null, __('messages.contact_message_spam_reported'));
    }

    public function reply(Request $request, int $id)
    {
        $message = $this->findOwnedMessage($request, $id);
        if (! $message) {
            return $this->errorResponse(__('messages.contact_message_not_found'), 404);
        }

        if ($message->is_spam || $message->hidden_at) {
            return $this->errorResponse(__('messages.contact_message_spam_hidden'), 422);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:10000'],
        ]);

        $reply = ContactMessageReply::create([
            'contact_message_id' => $message->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'delivery_status' => 'pending',
        ]);

        SendContactReplyJob::dispatch($reply->id);

        return $this->successResponse(
            $this->formatReply($reply),
            __('messages.contact_reply_queued'),
            201
        );
    }

    public function retryReply(Request $request, int $id, int $replyId)
    {
        $message = $this->findOwnedMessage($request, $id);
        if (! $message) {
            return $this->errorResponse(__('messages.contact_message_not_found'), 404);
        }

        $reply = $message->replies()->where('id', $replyId)->first();
        if (! $reply) {
            return $this->errorResponse(__('messages.contact_reply_not_found'), 404);
        }

        if ($reply->delivery_status !== 'failed') {
            return $this->errorResponse(__('messages.contact_reply_not_retryable'), 422);
        }

        $reply->forceFill([
            'delivery_status' => 'pending',
            'error_message' => null,
            'failed_at' => null,
        ])->save();

        SendContactReplyJob::dispatch($reply->id);

        return $this->successResponse(
            $this->formatReply($reply->fresh()),
            __('messages.contact_reply_queued')
        );
    }

    private function findOwnedMessage(Request $request, int $id)
    {
        $profile = $this->repository->findForUser($request->user()->id);
        if (! $profile || ! $profile->inboxIsEnabled()) {
            return null;
        }

        return $profile->contactMessages()->visible()->where('id', $id)->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMessage($message): array
    {
        return [
            'id' => $message->id,
            'name' => $message->name,
            'email' => $message->email,
            'subject' => $message->subject,
            'message' => $message->message,
            'is_read' => $message->isRead(),
            'is_spam' => (bool) $message->is_spam,
            'created_at' => $message->created_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
            'replies' => $message->relationLoaded('replies')
                ? $message->replies->map(fn ($r) => $this->formatReply($r))->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReply(ContactMessageReply $reply): array
    {
        return [
            'id' => $reply->id,
            'body' => $reply->body,
            'from_email' => $reply->from_email,
            'from_name' => $reply->from_name,
            'mail_mode' => $reply->mail_mode,
            'delivery_status' => $reply->delivery_status,
            'error_message' => $reply->error_message,
            'queued_at' => $reply->queued_at?->toIso8601String(),
            'sent_at' => $reply->sent_at?->toIso8601String(),
            'failed_at' => $reply->failed_at?->toIso8601String(),
            'created_at' => $reply->created_at?->toIso8601String(),
        ];
    }
}
