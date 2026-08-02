<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Merriweather', 'Noto Sans Arabic', serif; font-size: 11pt; line-height: 1.7; color: #1f2937; }
        .page { width: 210mm; min-height: 297mm; padding: 22mm 24mm; background: #fff; }
        .header { text-align: center; margin-bottom: 22px; page-break-inside: avoid; }
        .name { font-family: 'Playfair Display', serif; margin: 0 0 6px; font-size: 24pt; font-weight: 700; color: #111827; }
        .title { margin: 0 0 10px; font-size: 10.5pt; color: #4b5563; }
        .double-rule { border: none; border-top: 2px solid #111; border-bottom: 1px solid #111; height: 4px; margin: 0 auto 12px; width: 100%; }
        .contact { font-size: 9.5pt; color: #4b5563; }
        .contact span + span::before { content: " · "; }
        .date { margin: 18px 0; font-size: 10pt; color: #6b7280; }
        .recipient p { margin: 0 0 2px; }
        .recipient-name { font-weight: 700; }
        .subject { margin: 18px 0; font-weight: 700; }
        .body p { margin: 0 0 12px; text-align: justify; }
        .closing { margin-top: 28px; page-break-inside: avoid; }
        .closing p { margin: 0 0 4px; }
        .signature { margin-top: 28px; font-family: 'Playfair Display', serif; font-size: 13pt; font-weight: 700; }
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

    <header class="header">
        @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
        @if(!empty($userData['jobTitle']))<p class="title">{{ $userData['jobTitle'] }}</p>@endif
        <div class="double-rule" aria-hidden="true"></div>
        @if(!empty($contactParts))
            <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
        @endif
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
