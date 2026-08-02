<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Roboto', 'Noto Sans Arabic', sans-serif; font-size: 10.5pt; line-height: 1.55; color: #1e293b; }
        .page { width: 210mm; min-height: 297mm; padding: 16mm 18mm; background: #fff; }
        .letterhead { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1e3a5f; page-break-inside: avoid; }
        .name { font-family: 'Roboto Condensed', sans-serif; margin: 0 0 2px; font-size: 20pt; font-weight: 700; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.02em; }
        .title { margin: 0; font-size: 10pt; font-weight: 500; color: #475569; }
        .right-meta { text-align: right; font-size: 9pt; color: #64748b; }
        .right-meta p { margin: 0 0 2px; }
        .date { text-align: right; margin: 0 0 12px; font-size: 9.5pt; color: #64748b; }
        .recipient { margin-bottom: 12px; }
        .recipient p { margin: 0 0 1px; font-size: 10pt; }
        .recipient-name { font-weight: 700; }
        .subject { margin: 0 0 12px; font-family: 'Roboto Condensed', sans-serif; font-weight: 700; font-size: 11pt; color: #1e3a5f; }
        .body p { margin: 0 0 10px; text-align: justify; }
        .closing { margin-top: 20px; page-break-inside: avoid; }
        .signature { margin-top: 16px; font-family: 'Roboto Condensed', sans-serif; font-weight: 700; color: #1e3a5f; }
        [dir="rtl"] .letterhead { flex-direction: row-reverse; }
        [dir="rtl"] .right-meta, [dir="rtl"] .date { text-align: left; }
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

    <header class="letterhead">
        <div>
            @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
            @if(!empty($userData['jobTitle']))<p class="title">{{ $userData['jobTitle'] }}</p>@endif
        </div>
        <div class="right-meta">
            @foreach($contactParts as $part)
                <p>{{ $part }}</p>
            @endforeach
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
            @if(!empty($userData['jobTitle']))<p>{{ $userData['jobTitle'] }}</p>@endif
        </section>
    @endif
</x-cover-letter-layout>
