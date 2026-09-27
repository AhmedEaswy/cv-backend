@php
    /** @var \App\Models\ContactMessage $inquiry */
    /** @var \App\Models\ContactMessageReply $reply */
    /** @var string $appName */
@endphp
@extends('emails.layout', ['appName' => $appName])

@section('content')
    <p>{{ $reply->body }}</p>

    <hr style="border: none; border-top: 1px solid #e5e5e5; margin: 1.5rem 0;">

    <p class="muted">{{ __('messages.contact_reply_quoted_intro', ['name' => $inquiry->name]) }}</p>
    @if ($inquiry->subject)
        <p style="margin: 0 0 4px;">
            <strong>{{ __('messages.contact_email.subject') }}:</strong>
            {{ $inquiry->subject }}
        </p>
    @endif
    <div class="quote">{{ $inquiry->message }}</div>
@endsection
