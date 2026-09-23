@php
    /** @var string $code */
    /** @var string $purpose */
    /** @var string|null $userName */
    /** @var string $appName */
    /** @var int $ttlMinutes */
@endphp
@extends('emails.layout', ['appName' => $appName])

@section('content')
    @if ($purpose === 'password_reset')
        <h1>{{ __('messages.otp_reset_heading') }}</h1>
        <p>{{ __('messages.otp_reset_intro', ['name' => $userName ?: __('messages.otp_greeting_fallback'), 'app' => $appName]) }}</p>
    @else
        <h1>{{ __('messages.otp_verify_heading') }}</h1>
        <p>{{ __('messages.otp_verify_intro', ['name' => $userName ?: __('messages.otp_greeting_fallback'), 'app' => $appName]) }}</p>
    @endif

    <div class="quote" style="text-align: center; letter-spacing: 0.35em; font-size: 28px; font-weight: 700; color: #130e21;">
        {{ $code }}
    </div>

    <p>{{ __('messages.otp_expires', ['minutes' => $ttlMinutes]) }}</p>
    <p class="muted">{{ __('messages.otp_ignore') }}</p>
    <p>{{ __('messages.verify_email_signature', ['app' => $appName]) }}</p>
@endsection
