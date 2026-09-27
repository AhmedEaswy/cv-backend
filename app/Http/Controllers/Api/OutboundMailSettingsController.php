<?php

namespace App\Http\Controllers\Api;

use App\Models\UserOutboundMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OutboundMailSettingsController extends BaseApiController
{
    public function show(Request $request)
    {
        $record = $this->recordFor($request);

        return $this->successResponse(
            $this->format($record),
            __('messages.outbound_mail_retrieved')
        );
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'domain' => ['sometimes', 'nullable', 'string', 'max:191'],
            'from_email' => ['sometimes', 'nullable', 'string', 'email:rfc', 'max:191'],
            'from_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'smtp_host' => ['sometimes', 'nullable', 'string', 'max:255'],
            'smtp_port' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['sometimes', 'nullable', 'string', Rule::in(['tls', 'ssl', 'null', ''])],
            'smtp_username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'smtp_password' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $record = $this->recordFor($request);

        if (array_key_exists('domain', $validated) && $validated['domain'] !== $record->domain) {
            $record->dns_verified_at = null;
            $record->smtp_verified_at = null;
            $record->dns_verification_token = Str::random(32);
        }

        if (! $record->dns_verification_token) {
            $record->dns_verification_token = Str::random(32);
        }

        if (array_key_exists('smtp_encryption', $validated) && $validated['smtp_encryption'] === 'null') {
            $validated['smtp_encryption'] = null;
        }

        $record->fill($validated);
        $record->save();

        return $this->successResponse(
            $this->format($record->fresh()),
            __('messages.outbound_mail_saved')
        );
    }

    public function verifyDns(Request $request)
    {
        $record = $this->recordFor($request);

        if (! filled($record->domain)) {
            return $this->errorResponse(__('messages.outbound_mail_domain_required'), 422);
        }

        $token = $record->dns_verification_token;
        $expected = 'cv-verify='.$token;
        $domain = strtolower((string) $record->domain);

        $hosts = ["_cv-mail.{$domain}", $domain];
        $verified = false;

        foreach ($hosts as $host) {
            $records = @dns_get_record($host, DNS_TXT) ?: [];
            foreach ($records as $row) {
                $txt = $row['txt'] ?? '';
                if (is_string($txt) && str_contains($txt, $expected)) {
                    $verified = true;
                    break 2;
                }
            }
        }

        if (! $verified) {
            return $this->errorResponse(__('messages.outbound_mail_dns_failed'), 422);
        }

        $record->forceFill(['dns_verified_at' => now()])->save();

        return $this->successResponse(
            $this->format($record->fresh()),
            __('messages.outbound_mail_dns_verified')
        );
    }

    public function test(Request $request)
    {
        $record = $this->recordFor($request);

        if ($record->dns_verified_at === null) {
            return $this->errorResponse(__('messages.outbound_mail_dns_required'), 422);
        }

        if (! filled($record->smtp_host) || ! filled($record->smtp_username) || ! filled($record->smtp_password)) {
            return $this->errorResponse(__('messages.outbound_mail_smtp_incomplete'), 422);
        }

        $fromEmail = $record->from_email ?: $request->user()->email;
        $fromName = $record->from_name ?: $request->user()->full_name;

        if ($record->domain && $fromEmail) {
            $emailDomain = Str::after($fromEmail, '@');
            if (strtolower($emailDomain) !== strtolower((string) $record->domain)) {
                return $this->errorResponse(__('messages.outbound_mail_from_domain_mismatch'), 422);
            }
        }

        $mailerName = 'outbound_test_'.$request->user()->id;

        config([
            "mail.mailers.{$mailerName}" => [
                'transport' => 'smtp',
                'host' => $record->smtp_host,
                'port' => $record->smtp_port,
                'encryption' => $record->smtp_encryption ?: null,
                'username' => $record->smtp_username,
                'password' => $record->smtp_password,
                'timeout' => null,
            ],
        ]);

        try {
            Mail::mailer($mailerName)->raw(
                __('messages.outbound_mail_test_body'),
                function ($message) use ($request, $fromEmail, $fromName) {
                    $message->to($request->user()->email)
                        ->from($fromEmail, $fromName)
                        ->subject(__('messages.outbound_mail_test_subject'));
                }
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(__('messages.outbound_mail_test_failed', ['error' => $e->getMessage()]), 422);
        }

        $record->forceFill(['smtp_verified_at' => now()])->save();

        return $this->successResponse(
            $this->format($record->fresh()),
            __('messages.outbound_mail_test_sent')
        );
    }

    private function recordFor(Request $request): UserOutboundMail
    {
        return UserOutboundMail::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'dns_verification_token' => Str::random(32),
                'is_active' => true,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function format(UserOutboundMail $record): array
    {
        return [
            'domain' => $record->domain,
            'from_email' => $record->from_email,
            'from_name' => $record->from_name,
            'dns_verification_token' => $record->dns_verification_token,
            'dns_verified_at' => $record->dns_verified_at?->toIso8601String(),
            'smtp_host' => $record->smtp_host,
            'smtp_port' => $record->smtp_port,
            'smtp_encryption' => $record->smtp_encryption,
            'smtp_username' => $record->smtp_username,
            'has_smtp_password' => filled($record->getAttributes()['smtp_password'] ?? null),
            'smtp_verified_at' => $record->smtp_verified_at?->toIso8601String(),
            'is_active' => (bool) $record->is_active,
            'ready_for_sending' => $record->isReadyForSending(),
            'dns_txt_host' => $record->domain ? '_cv-mail.'.$record->domain : null,
            'dns_txt_value' => $record->dns_verification_token
                ? 'cv-verify='.$record->dns_verification_token
                : null,
        ];
    }
}
