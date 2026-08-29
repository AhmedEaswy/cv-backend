<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\PublicProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PublicProfile $profile,
        public readonly ContactMessage $inquiry,
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->inquiry->subject
            ? $this->inquiry->subject
            : __('messages.contact_subject_default', [
                'name' => $this->inquiry->name,
            ]);

        return new Envelope(
            subject: '['.config('app.name', 'CV').'] '.$subject,
            replyTo: [$this->inquiry->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message-received',
            with: [
                'profileUrl' => $this->profile->public_url,
                'inquiry' => $this->inquiry,
                'appName' => config('app.name', 'CV'),
            ],
        );
    }
}
