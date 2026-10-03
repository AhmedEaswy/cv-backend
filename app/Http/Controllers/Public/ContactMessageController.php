<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\PortalContactMessageRequest;
use App\Models\ContactMessage;
use App\Repositories\PublicProfileRepository;
use App\Services\Contact\ContactMessageDeliveryService;
use App\Services\Contact\ContactModerationService;
use App\Services\Contact\ContactSpamService;
use App\Services\Contact\TurnstileVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class ContactMessageController extends Controller
{
    public function __construct(
        private readonly PublicProfileRepository $repository,
        private readonly ContactSpamService $spamService,
        private readonly ContactModerationService $moderationService,
        private readonly ContactMessageDeliveryService $deliveryService,
        private readonly TurnstileVerifier $turnstile,
    ) {
    }

    public function store(PortalContactMessageRequest $request, string $slug): RedirectResponse
    {
        $email = strtolower((string) $request->input('email'));
        $ip = $request->ip();

        if (! empty($request->input('hp_check'))) {
            $this->spamService->recordHoneypot($email, $ip);

            return $this->fakeSuccess($slug);
        }

        if ($this->spamService->isBlocked($email, $ip)) {
            return $this->fakeSuccess($slug);
        }

        $turnstileToken = $request->input('cf_turnstile_response')
            ?? $request->input('cf-turnstile-response');

        if (! $this->turnstile->verify(
            is_string($turnstileToken) ? $turnstileToken : null,
            $ip,
        )) {
            return back()->withErrors([
                'message' => __('messages.contact_turnstile_failed'),
            ])->withInput();
        }

        $throttleKey = 'contact-sender:'.sha1($email);

        $maxAttempts = (int) config('contact.sender_rate_limit.max_attempts', 3);
        $decaySeconds = (int) config('contact.sender_rate_limit.decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
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
        $moderationStatus = $this->moderationService->initialStatus($data['message'], $profile->id, $email);

        $message = ContactMessage::create([
            'public_profile_id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'ip_address' => $ip,
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'moderation_status' => $moderationStatus,
        ]);

        if ($moderationStatus->value === 'approved') {
            try {
                $this->deliveryService->deliver($message, $profile);
            } catch (Throwable $e) {
                Log::error('Contact message delivery failed', [
                    'message_id' => $message->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->spamService->maybeAutoBlockCrossProfile($email, $ip);

        RateLimiter::hit($throttleKey, $decaySeconds);

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
