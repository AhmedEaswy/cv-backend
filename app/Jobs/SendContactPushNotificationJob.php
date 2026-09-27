<?php

namespace App\Jobs;

use App\Models\ContactMessage;
use App\Models\User;
use App\Services\Fcm\FcmPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendContactPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $messageId,
        public readonly int $userId,
    ) {
    }

    public function handle(FcmPushService $fcm): void
    {
        $user = User::query()->find($this->userId);
        if (! $user || ! $user->notify_contact_push) {
            return;
        }

        $message = ContactMessage::query()->with('publicProfile')->find($this->messageId);
        if (! $message) {
            return;
        }

        $title = config('app.name', 'CV');
        $subject = $message->subject ?: __('messages.contact_subject_default', ['name' => $message->name]);
        $body = Str::limit((string) $message->message, 120);

        $data = [
            'type' => 'contact_message',
            'message_id' => (string) $message->id,
            'deep_link' => 'cv://inbox/'.$message->id,
            'profile_slug' => (string) ($message->publicProfile?->slug ?? ''),
        ];

        foreach ($user->devicePushTokens as $device) {
            $fcm->sendToToken($device, $title, $subject.': '.$body, $data);
        }
    }
}
