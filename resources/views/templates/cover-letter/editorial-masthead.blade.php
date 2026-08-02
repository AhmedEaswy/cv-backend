<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Source Sans 3', 'Noto Sans Arabic', sans-serif; font-size: 11pt; line-height: 1.65; color: #1c1917; }
        .page { width: 210mm; min-height: 297mm; padding: 18mm 20mm; background: #fff; }
        .masthead { border-bottom: 3px double #1c1917; padding-bottom: 12px; margin-bottom: 18px; page-break-inside: avoid; }
        .name { font-family: 'Newsreader', serif; margin: 0; font-size: 32pt; font-weight: 700; line-height: 1; letter-spacing: -0.02em; }
        .meta-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 10px; font-size: 9pt; color: #57534e; text-transform: uppercase; letter-spacing: 0.06em; }
        .date { margin: 0 0 16px; font-size: 10pt; color: #78716c; }
        .recipient p { margin: 0 0 2px; }
        .recipient-name { font-weight: 700; }
        .subject { margin: 16px 0; font-size: 10.5pt; color: #57534e; border-bottom: 1px solid #d6d3d1; padding-bottom: 8px; font-weight: 600; }
        .body p { margin: 0 0 12px; text-align: justify; }
        .closing { margin-top: 26px; page-break-inside: avoid; }
        .signature { margin-top: 22px; font-family: 'Newsreader', serif; font-size: 14pt; font-weight: 700; }
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

    <header class="masthead">
        @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
        <div class="meta-row">
            <span>{{ $userData['jobTitle'] ?? '' }}</span>
            <span>{{ implode(' · ', $contactParts) }}</span>
        </div>
    </header>

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
</x-cover-letter-layout>
