<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Work+Sans:wght@400;500&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Work Sans', 'Noto Sans Arabic', sans-serif; font-size: 11pt; line-height: 1.65; color: #18181b; }
        .page { width: 210mm; min-height: 297mm; padding: 0; background: #fff; }
        .banner { background: #27272a; color: #fafafa; padding: 16mm 18mm 14mm; page-break-inside: avoid; }
        .name { font-family: 'Space Grotesk', sans-serif; margin: 0 0 6px; font-size: 28pt; font-weight: 700; letter-spacing: -0.03em; }
        .title { margin: 0 0 12px; font-size: 11pt; color: #a1a1aa; font-weight: 500; }
        .contact { font-size: 9.5pt; color: #d4d4d8; }
        .contact span + span::before { content: "  |  "; color: #71717a; }
        .shell { padding: 14mm 18mm 20mm; }
        .date { margin: 0 0 16px; font-size: 10pt; color: #71717a; }
        .recipient p { margin: 0 0 2px; }
        .recipient-name { font-weight: 700; }
        .subject { margin: 16px 0; padding-bottom: 8px; border-bottom: 2px solid #27272a; font-family: 'Space Grotesk', sans-serif; font-weight: 700; }
        .body p { margin: 0 0 12px; }
        .closing { margin-top: 26px; page-break-inside: avoid; }
        .signature { margin-top: 20px; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 12pt; }
        </style>
    @endslot

    @php
        $userData = $coverLetter['user_data'] ?? [];
        $fullName = trim(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? ''));
        $contactParts = array_values(array_filter([$userData['email'] ?? null, $userData['phone'] ?? null, $userData['address'] ?? null]));
        $recipientLines = array_values(array_filter([$userData['recipientName'] ?? null, $userData['recipientTitle'] ?? null, $userData['recipientCompany'] ?? $userData['companyName'] ?? null]));
        $bodyParagraphs = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $userData['body'] ?? '') ?: [])));
        $letterDate = now()->translatedFormat('F j, Y');
    @endphp

    <header class="banner">
        @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
        @if(!empty($userData['jobTitle']))<p class="title">{{ $userData['jobTitle'] }}</p>@endif
        @if(!empty($contactParts))
            <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
        @endif
    </header>

    <div class="shell">
        <p class="date">{{ $letterDate }}</p>

        @if(!empty($recipientLines))
            <section class="recipient">
                @foreach($recipientLines as $index => $line)
                    <p @class(['recipient-name' => $index === 0])>{{ $line }}</p>
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
    </div>
</x-cover-letter-layout>
