<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $code,
        public readonly string $purpose,
        public readonly ?string $userName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $subjectKey = $this->purpose === 'password_reset'
            ? 'messages.otp_reset_subject'
            : 'messages.otp_verify_subject';

        return new Envelope(
            subject: __($subjectKey, ['app' => config('app.name', 'CV')]),
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-otp',
            with: [
                'code' => $this->code,
                'purpose' => $this->purpose,
                'userName' => $this->userName,
                'appName' => config('app.name', 'CV'),
                'ttlMinutes' => 10,
            ],
        );
    }
}
