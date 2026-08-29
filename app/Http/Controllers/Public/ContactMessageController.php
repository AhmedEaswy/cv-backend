<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\PortalContactMessageRequest;
use App\Mail\ContactMessageReceivedMail;
use App\Models\ContactMessage;
use App\Repositories\PublicProfileRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class ContactMessageController extends Controller
{
    public function __construct(private readonly PublicProfileRepository $repository)
    {
    }

    public function store(PortalContactMessageRequest $request, string $slug): RedirectResponse
    {
        // Honeypot — bots fill the hidden field, real users don't.
        if (! empty($request->input('website'))) {
            return back()->with('status', __('messages.contact_message_sent'));
        }

        $throttleKey = 'contact:'.$request->ip().':'.strtolower((string) $request->input('email'));

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

        $recipient = $profile->contactRecipient();

        if (! $recipient) {
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
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        try {
            Mail::to($recipient)->send(new ContactMessageReceivedMail($profile, $message));
        } catch (Throwable $e) {
            Log::error('Contact form email failed', [
                'profile_id' => $profile->id,
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        return redirect()->to('/u/'.$profile->slug.'#contact-form')
            ->with('status', __('messages.contact_message_sent'));
    }
}
