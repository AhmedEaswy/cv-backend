@php
    $userData = $coverLetter['user_data'] ?? [];
    $fullName = trim(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? ''));
    $senderLines = array_filter([
        $fullName ?: null,
        $userData['address'] ?? null,
        $userData['email'] ?? null,
        $userData['phone'] ?? null,
    ]);
    $recipientLines = array_filter([
        $userData['recipientName'] ?? null,
        $userData['recipientTitle'] ?? null,
        $userData['recipientCompany'] ?? $userData['companyName'] ?? null,
    ]);
    $bodyParagraphs = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $userData['body'] ?? '')));
@endphp

@if(!empty($senderLines))
    <section class="sender-block">
        @foreach($senderLines as $line)
            <p>{{ $line }}</p>
        @endforeach
    </section>
@endif

@if(!empty($recipientLines))
    <section class="recipient-block">
        @foreach($recipientLines as $line)
            <p>{{ $line }}</p>
        @endforeach
    </section>
@endif

@if(!empty($userData['subject']))
    <section class="subject-block">
        <p><strong>{{ __('Subject') }}:</strong> {{ $userData['subject'] }}</p>
    </section>
@endif

@if(!empty($bodyParagraphs))
    <section class="body-block">
        @foreach($bodyParagraphs as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
    </section>
@elseif(!empty($userData['body']))
    <section class="body-block">
        <p>{{ $userData['body'] }}</p>
    </section>
@endif

@if(!empty($userData['closing']) || $fullName)
    <section class="closing-block">
        @if(!empty($userData['closing']))
            <p>{{ $userData['closing'] }}</p>
        @else
            <p>{{ __('Sincerely') }},</p>
        @endif
        @if($fullName)
            <p class="signature">{{ $fullName }}</p>
        @endif
        @if(!empty($userData['jobTitle']))
            <p class="signature-title">{{ $userData['jobTitle'] }}</p>
        @endif
    </section>
@endif
