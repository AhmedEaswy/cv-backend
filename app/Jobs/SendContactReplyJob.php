<?php

namespace App\Jobs;

use App\Mail\ContactMessageReplyMail;
use App\Models\ContactMessageReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendContactReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $replyId,
    ) {
    }

    public function handle(): void
    {
        $reply = ContactMessageReply::query()
            ->with(['contactMessage.publicProfile.user.outboundMail'])
            ->find($this->replyId);

        if (! $reply || ! $reply->contactMessage) {
            return;
        }

        if (in_array($reply->delivery_status, ['sent'], true)) {
            return;
        }

        $reply->forceFill([
            'delivery_status' => 'queued',
            'queued_at' => now(),
        ])->save();

        $inquiry = $reply->contactMessage;
        $owner = $reply->user;
        $outbound = $owner?->outboundMail;

        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name');
        $mailMode = 'company';

        if ($outbound?->isReadyForSending()) {
            $fromEmail = $outbound->from_email;
            $fromName = $outbound->from_name ?: $fromName;
            $mailMode = 'custom_smtp';
        }

        $reply->forceFill([
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'mail_mode' => $mailMode,
        ])->save();

        try {
            $replyToEmail = null;
            $replyToName = null;

            if ($mailMode === 'company') {
                $profile = $inquiry->publicProfile;
                $replyToEmail = $profile?->contactRecipient() ?: $owner?->email;
                $replyToName = $owner?->full_name ?: $fromName;
            }

            $mailable = new ContactMessageReplyMail(
                $inquiry,
                $reply,
                $replyToEmail,
                $replyToName,
            );

            if ($mailMode === 'custom_smtp' && $outbound) {
                $this->sendViaCustomSmtp($outbound, $mailable, $inquiry->email, $fromEmail, $fromName);
            } else {
                Mail::to($inquiry->email)->send($mailable);
            }

            $reply->forceFill([
                'delivery_status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
                'failed_at' => null,
            ])->save();
        } catch (Throwable $e) {
            Log::error('Contact reply send failed', [
                'reply_id' => $reply->id,
                'error' => $e->getMessage(),
            ]);

            $reply->forceFill([
                'delivery_status' => 'failed',
                'failed_at' => now(),
                'error_message' => Str::limit($e->getMessage(), 2000),
            ])->save();
        }
    }

    private function sendViaCustomSmtp(
        $outbound,
        ContactMessageReplyMail $mailable,
        string $to,
        ?string $fromEmail,
        ?string $fromName,
    ): void {
        $mailerName = 'contact_reply_'.$outbound->user_id;

        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $outbound->smtp_host,
            'port' => $outbound->smtp_port,
            'encryption' => $outbound->smtp_encryption ?: null,
            'username' => $outbound->smtp_username,
            'password' => $outbound->smtp_password,
            'timeout' => null,
        ]);

        $mailable->from($fromEmail, (string) ($fromName ?? ''));

        Mail::mailer($mailerName)->to($to)->send($mailable);
    }
}
