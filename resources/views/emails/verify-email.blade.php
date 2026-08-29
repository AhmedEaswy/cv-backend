@php
    /** @var string $userName */
    /** @var string $verificationUrl */
    /** @var string $appName */
@endphp
@extends('emails.layout', ['appName' => $appName])

@section('content')
    <h1>{{ __('messages.verify.email.heading') }}</h1>
    <p>{{ __('messages.verify_email_intro', ['name' => $userName, 'app' => $appName]) }}</p>
    <div class="button-wrap">
        <a href="{{ $verificationUrl }}" class="button" rel="noopener">
            {{ __('messages.verify.email.action') }}
        </a>
    </div>
    <p>{{ __('messages.verify.email.help') }}</p>
    <div class="divider"></div>
    <p class="muted">{{ __('messages.verify.email.link_alt') }}<br>{{ $verificationUrl }}</p>
    <p>{{ __('messages.verify_email_signature', ['app' => $appName]) }}</p>
@endsection
