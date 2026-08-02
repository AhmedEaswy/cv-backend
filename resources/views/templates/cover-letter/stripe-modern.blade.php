<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'IBM Plex Sans', 'Noto Sans Arabic', sans-serif; font-size: 11pt; line-height: 1.65; color: #134e4a; }
        .page { width: 210mm; min-height: 297mm; padding: 0; background: #fff; display: flex; }
        .stripe { width: 12mm; background: #0f766e; flex-shrink: 0; }
        .shell { flex: 1; padding: 18mm 18mm 20mm 14mm; }
        .name { margin: 0 0 4px; font-size: 22pt; font-weight: 700; color: #0f766e; }
        .title { margin: 0 0 10px; font-size: 11pt; font-weight: 500; color: #5eead4; color: #115e59; }
        .contact { font-size: 9.5pt; color: #54716d; margin-bottom: 22px; }
        .contact span + span::before { content: "  ·  "; }
        .date { margin: 0 0 16px; font-size: 10pt; color: #6b7280; }
        .recipient { margin-bottom: 16px; }
        .recipient p { margin: 0 0 2px; }
        .recipient-name { font-weight: 700; }
        .subject { margin: 0 0 16px; padding: 8px 12px; background: #f0fdfa; border-inline-start: 3px solid #0f766e; font-weight: 600; font-size: 10.5pt; }
        .body p { margin: 0 0 12px; }
        .closing { margin-top: 24px; page-break-inside: avoid; }
        .signature { margin-top: 20px; font-weight: 700; color: #0f766e; }
        [dir="rtl"] .page { flex-direction: row-reverse; }
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

    <div class="stripe" aria-hidden="true"></div>
    <div class="shell">
        @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
        @if(!empty($userData['jobTitle']))<p class="title">{{ $userData['jobTitle'] }}</p>@endif
        @if(!empty($contactParts))
            <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
        @endif

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
