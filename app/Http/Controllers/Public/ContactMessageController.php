<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\PortalContactMessageRequest;
use App\Jobs\SendContactPushNotificationJob;
use App\Mail\ContactMessageReceivedMail;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageReceivedNotification;
use App\Repositories\PublicProfileRepository;
use App\Services\Contact\ContactSpamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class ContactMessageController extends Controller
{
    public function __construct(
        private readonly PublicProfileRepository $repository,
        private readonly ContactSpamService $spamService,
    ) {
    }

    public function store(PortalContactMessageRequest $request, string $slug): RedirectResponse
    {
        $email = strtolower((string) $request->input('email'));
        $ip = $request->ip();

        if (! empty($request->input('website'))) {
            $this->spamService->recordHoneypot($email, $ip);

            return $this->fakeSuccess($slug);
        }

        if ($this->spamService->isBlocked($email, $ip)) {
            return $this->fakeSuccess($slug);
        }

        $throttleKey = 'contact:'.$ip.':'.$email;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => __('messages.contact_throttled', ['seconds' => $seconds]),
            ])->withInput();
        }

        $profile = $this->repository->findPublicBySlug($slug);

        if (! $profile || ! $profile->showsContactForm()) {
            abort(404, __('messages.public_profile_not_found'));
        }

        $owner = $profile->user;
        $recipient = $profile->contactRecipient();

        if (! $recipient || ! $owner) {
            Log::warning('Contact form submitted with no recipient', ['profile_id' => $profile->id]);

            return back()->withErrors(['email' => __('messages.contact_unavailable')]);
        }

        $data = $request->validated();

        $message = ContactMessage::create([
            'public_profile_id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'ip_address' => $ip,
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

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

        $this->spamService->maybeAutoBlockCrossProfile($email, $ip);

        RateLimiter::hit($throttleKey, 300);

        return redirect()->to($profile->preferredPublicUrl().'#contact-form')
            ->with('status', __('messages.contact_message_sent'));
    }

    private function fakeSuccess(string $slug): RedirectResponse
    {
        $profile = $this->repository->findPublicBySlug($slug);
        $target = $profile
            ? $profile->preferredPublicUrl().'#contact-form'
            : url('/u/'.$slug.'#contact-form');

        return redirect()->to($target)
            ->with('status', __('messages.contact_message_sent'));
    }
}
