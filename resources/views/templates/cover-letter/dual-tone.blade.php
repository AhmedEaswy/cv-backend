<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Manrope', 'Noto Sans Arabic', sans-serif; font-size: 11pt; line-height: 1.65; color: #1e3a5f; }
        .page { width: 210mm; min-height: 297mm; padding: 0; background: #fff; }
        .band-top { background: #1e3a5f; color: #fff; padding: 14mm 20mm 10mm; }
        .band-bottom { background: #3b82c4; height: 8px; }
        .name { margin: 0 0 4px; font-size: 24pt; font-weight: 700; }
        .title { margin: 0 0 10px; font-size: 11pt; opacity: 0.9; }
        .contact { font-size: 9.5pt; opacity: 0.85; }
        .contact span + span::before { content: "  ·  "; }
        .shell { padding: 14mm 20mm 20mm; }
        .date { margin: 0 0 16px; font-size: 10pt; color: #64748b; }
        .recipient p { margin: 0 0 2px; }
        .recipient-name { font-weight: 700; }
        .subject { margin: 16px 0; font-weight: 700; color: #1e3a5f; }
        .body p { margin: 0 0 12px; }
        .closing { margin-top: 26px; page-break-inside: avoid; }
        .signature { margin-top: 20px; font-weight: 700; color: #1e3a5f; }
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

    <div class="band-top">
        @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
        @if(!empty($userData['jobTitle']))<p class="title">{{ $userData['jobTitle'] }}</p>@endif
        @if(!empty($contactParts))
            <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
        @endif
    </div>
    <div class="band-bottom" aria-hidden="true"></div>

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
