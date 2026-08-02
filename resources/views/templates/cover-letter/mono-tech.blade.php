<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'IBM Plex Mono', 'Noto Sans Arabic', monospace; font-size: 10pt; line-height: 1.7; color: #111827; }
        .page { width: 210mm; min-height: 297mm; padding: 18mm 20mm; background: #fafafa; }
        .meta-stack { margin-bottom: 24px; border-left: 2px solid #111827; padding-left: 14px; page-break-inside: avoid; }
        .name { margin: 0 0 4px; font-size: 16pt; font-weight: 600; }
        .title { margin: 0 0 10px; font-size: 9.5pt; color: #4b5563; }
        .meta-stack p { margin: 0 0 2px; font-size: 9pt; color: #374151; }
        .date { margin: 0 0 16px; font-size: 9pt; color: #6b7280; }
        .prompt { color: #059669; margin-right: 6px; }
        .recipient p { margin: 0 0 2px; }
        .subject { margin: 16px 0; font-weight: 600; font-size: 9.5pt; }
        .body p { margin: 0 0 12px; }
        .closing { margin-top: 28px; page-break-inside: avoid; }
        .signature { margin-top: 18px; font-weight: 600; }
        </style>
    @endslot

    @php
        $userData = $coverLetter['user_data'] ?? [];
        $fullName = trim(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? ''));
        $recipientLines = array_values(array_filter([$userData['recipientName'] ?? null, $userData['recipientTitle'] ?? null, $userData['recipientCompany'] ?? $userData['companyName'] ?? null]));
        $bodyParagraphs = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $userData['body'] ?? '') ?: [])));
        $letterDate = now()->translatedFormat('F j, Y');
    @endphp

    <header class="meta-stack">
        @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
        @if(!empty($userData['jobTitle']))<p class="title">// {{ $userData['jobTitle'] }}</p>@endif
        @foreach(array_filter([$userData['email'] ?? null, $userData['phone'] ?? null, $userData['address'] ?? null]) as $c)
            <p>{{ $c }}</p>
        @endforeach
    </header>

    <p class="date"><span class="prompt">$</span>date — {{ $letterDate }}</p>

    @if(!empty($recipientLines))
        <section class="recipient">
            @foreach($recipientLines as $line)
                <p>{{ $line }}</p>
            @endforeach
        </section>
    @endif

    @if(!empty($userData['subject']))
        <p class="subject">{{ __('Subject') }}: {{ $userData['subject'] }}</p>
    @endif

    @if(!empty($bodyParagraphs))
        <section class="body">@foreach($bodyParagraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach</section>
    @elseif(!empty($userData['body']))
        <section class="body"><p>{{ $userData['body'] }}</p></section>
    @endif

    @if(!empty($userData['closing']) || $fullName)
        <section class="closing">
            <p>{{ $userData['closing'] ?? __('Sincerely').',' }}</p>
            @if($fullName)<p class="signature">{{ $fullName }}</p>@endif
        </section>
    @endif
</x-cover-letter-layout>
