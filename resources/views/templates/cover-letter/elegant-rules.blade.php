<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Inter', 'Noto Sans Arabic', sans-serif; font-size: 10.5pt; line-height: 1.75; color: #292524; }
        .page { width: 210mm; min-height: 297mm; padding: 24mm 28mm; background: #fff; }
        .label { font-size: 8pt; letter-spacing: 0.18em; text-transform: uppercase; color: #a8a29e; margin: 0 0 6px; }
        .name { font-family: 'Cormorant Garamond', serif; margin: 0 0 4px; font-size: 28pt; font-weight: 600; }
        .title { margin: 0 0 14px; font-size: 10pt; color: #78716c; }
        .hairline { border: none; border-top: 0.5px solid #d6d3d1; margin: 16px 0; }
        .contact { font-size: 9pt; color: #57534e; }
        .contact span + span::before { content: "   "; }
        .date { margin: 0 0 18px; font-size: 9.5pt; color: #78716c; }
        .recipient p { margin: 0 0 2px; }
        .recipient-name { font-weight: 500; }
        .subject { margin: 18px 0; font-size: 10pt; letter-spacing: 0.04em; }
        .body p { margin: 0 0 14px; text-align: justify; }
        .closing { margin-top: 32px; page-break-inside: avoid; }
        .signature { margin-top: 28px; font-family: 'Cormorant Garamond', serif; font-size: 16pt; }
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

    <p class="label">{{ __('Cover Letter') }}</p>
    @if($fullName)<h1 class="name">{{ $fullName }}</h1>@endif
    @if(!empty($userData['jobTitle']))<p class="title">{{ $userData['jobTitle'] }}</p>@endif
    <hr class="hairline">
    @if(!empty($contactParts))
        <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
    @endif
    <hr class="hairline">

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
