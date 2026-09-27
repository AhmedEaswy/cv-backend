<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use App\Models\PublicProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ContactMessageReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ContactMessage $message,
        public readonly PublicProfile $profile,
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'contact_message',
            'message_id' => $this->message->id,
            'name' => $this->message->name,
            'subject' => $this->message->subject,
            'preview' => Str::limit((string) $this->message->message, 120),
            'profile_slug' => $this->profile->slug,
        ];
    }
}
