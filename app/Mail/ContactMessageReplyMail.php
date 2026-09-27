<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ContactMessage $inquiry,
        public readonly ContactMessageReply $reply,
        public readonly ?string $replyToEmail = null,
        public readonly ?string $replyToName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $original = $this->inquiry->subject ?: __('messages.contact_subject_default', [
            'name' => $this->inquiry->name,
        ]);

        $from = null;
        if ($this->reply->from_email) {
            $from = new Address(
                $this->reply->from_email,
                (string) ($this->reply->from_name ?? '')
            );
        }

        $replyTo = [];
        if ($this->replyToEmail) {
            $replyTo[] = new Address(
                $this->replyToEmail,
                (string) ($this->replyToName ?? '')
            );
        }

        return new Envelope(
            from: $from,
            replyTo: $replyTo,
            subject: 'Re: '.$original,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message-reply',
            with: [
                'inquiry' => $this->inquiry,
                'reply' => $this->reply,
                'appName' => config('app.name', 'CV'),
            ],
        );
    }
}
