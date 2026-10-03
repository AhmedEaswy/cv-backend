<?php

namespace App\Services\Contact;

use App\Jobs\SendContactPushNotificationJob;
use App\Mail\ContactMessageReceivedMail;
use App\Models\ContactMessage;
use App\Models\PublicProfile;
use App\Notifications\ContactMessageReceivedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactMessageDeliveryService
{
    public function deliver(ContactMessage $message, PublicProfile $profile): void
    {
        if ($message->delivered_at !== null) {
            return;
        }

        $owner = $profile->user;
        $recipient = $profile->contactRecipient();

        if (! $owner || ! $recipient) {
            Log::warning('Contact message delivery skipped: missing owner or recipient', [
                'message_id' => $message->id,
                'profile_id' => $profile->id,
            ]);

            return;
        }

        $owner->notify(new ContactMessageReceivedNotification($message, $profile));

        if ($owner->notify_contact_email) {
            try {
                Mail::to($recipient)->send(new ContactMessageReceivedMail($profile, $message));
            } catch (Throwable $e) {
                Log::error('Contact form email failed', [
                    'profile_id' => $profile->id,
                    'message_id' => $message->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($owner->notify_contact_push) {
            SendContactPushNotificationJob::dispatch($message->id, $owner->id);
        }

        $message->forceFill(['delivered_at' => now()])->save();
    }
}
