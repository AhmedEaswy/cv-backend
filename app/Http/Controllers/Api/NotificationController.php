<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends BaseApiController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->notifications()->orderByDesc('created_at');

        if (! $request->has('page')) {
            $items = $query->limit(50)->get()->map(fn ($n) => $this->format($n));

            return $this->successResponse([
                'data' => $items,
                'unread_count' => $user->unreadNotifications()->count(),
            ], __('messages.notifications_retrieved'));
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 20)));
        $paginator = $query->paginate($perPage);

        return $this->successResponse([
            'data' => $paginator->getCollection()->map(fn ($n) => $this->format($n))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
            'unread_count' => $user->unreadNotifications()->count(),
        ], __('messages.notifications_retrieved'));
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->errorResponse(__('messages.notification_not_found'), 404);
        }

        $notification->markAsRead();

        return $this->successResponse(
            $this->format($notification->fresh()),
            __('messages.notification_marked_read')
        );
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->successResponse(null, __('messages.notifications_all_read'));
    }

    /**
     * @return array<string, mixed>
     */
    private function format($notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? class_basename($notification->type),
            'data' => $data,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'title' => $this->titleFor($data),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function titleFor(array $data): string
    {
        if (($data['type'] ?? '') === 'contact_message') {
            $name = $data['name'] ?? __('messages.contact_anonymous');

            return __('messages.notification_contact_title', ['name' => $name]);
        }

        return Str::headline((string) ($data['type'] ?? 'notification'));
    }
}
