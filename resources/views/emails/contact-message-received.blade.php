@php
    /** @var \App\Models\PublicProfile $profile */
    /** @var \App\Models\ContactMessage $inquiry */
    /** @var string $profileUrl */
    /** @var string $appName */
@endphp
@extends('emails.layout', ['appName' => $appName])

@section('content')
    <h1>{{ __('messages.contact_email_heading', ['name' => $inquiry->name]) }}</h1>
    <p>{{ __('messages.contact_email_intro', ['app' => $appName, 'url' => $profileUrl]) }}</p>

    <p style="margin: 0 0 4px;">
        <strong>{{ __('messages.contact_email.from') }}:</strong>
        {{ $inquiry->name }} &lt;{{ $inquiry->email }}&gt;
    </p>
    @if ($inquiry->subject)
        <p style="margin: 0 0 4px;">
            <strong>{{ __('messages.contact_email.subject') }}:</strong>
            {{ $inquiry->subject }}
        </p>
    @endif

    <div class="quote">{{ $inquiry->message }}</div>

    <p class="muted">{{ __('messages.contact_email_footer', ['url' => $profileUrl]) }}</p>
@endsection
