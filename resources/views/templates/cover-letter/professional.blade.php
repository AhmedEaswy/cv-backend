<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <style>
        @page {
            margin: 0;
            size: A4;
        }
        html {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.65;
            color: #1f2937;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            background: #fff;
            overflow: hidden;
        }
        .accent-bar {
            height: 8px;
            background: #1e3a5f;
        }
        .letter-shell {
            padding: 18mm 22mm 22mm;
        }
        .header {
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 2px solid #1e3a5f;
            page-break-inside: avoid;
        }
        .header-name {
            margin: 0 0 6px;
            font-size: 22pt;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #1e3a5f;
            line-height: 1.2;
        }
        .header-title {
            margin: 0 0 12px;
            font-size: 11pt;
            font-weight: 600;
            color: #4b5563;
        }
        .contact-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 0;
            font-size: 9.5pt;
            color: #4b5563;
        }
        .contact-row span {
            display: inline-flex;
            align-items: center;
        }
        .contact-row span:not(:last-child)::after {
            content: "·";
            margin: 0 10px;
            color: #9ca3af;
        }
        [dir="rtl"] .contact-row {
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .meta-date {
            margin: 0 0 18px;
            font-size: 10pt;
            color: #6b7280;
        }
        .recipient-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .recipient-block p {
            margin: 0 0 2px;
            color: #1f2937;
        }
        .recipient-name {
            font-weight: 700;
        }
        .subject-block {
            margin-bottom: 18px;
            padding: 10px 14px;
            background: #f3f6fa;
            border-inline-start: 4px solid #1e3a5f;
            page-break-inside: avoid;
        }
        .subject-block p {
            margin: 0;
            font-size: 10.5pt;
            font-weight: 600;
            color: #1e3a5f;
        }
        .body-block {
            margin-bottom: 28px;
        }
        .body-block p {
            margin: 0 0 12px;
            text-align: justify;
        }
        .body-block p:last-child {
            margin-bottom: 0;
        }
        .closing-block {
            margin-top: 8px;
            page-break-inside: avoid;
        }
        .closing-block p {
            margin: 0 0 4px;
        }
        .signature-rule {
            width: 56px;
            height: 2px;
            margin: 22px 0 12px;
            background: #1e3a5f;
        }
        .signature {
            margin: 0;
            font-size: 12pt;
            font-weight: 700;
            color: #1e3a5f;
        }
        .signature-title {
            margin: 2px 0 0;
            font-size: 10pt;
            color: #6b7280;
        }
        </style>
    @endslot

    @php
        $userData = $coverLetter['user_data'] ?? [];
        $fullName = trim(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? ''));
        $contactParts = array_values(array_filter([
            $userData['email'] ?? null,
            $userData['phone'] ?? null,
            $userData['address'] ?? null,
        ]));
        $recipientLines = array_values(array_filter([
            $userData['recipientName'] ?? null,
            $userData['recipientTitle'] ?? null,
            $userData['recipientCompany'] ?? $userData['companyName'] ?? null,
        ]));
        $bodyParagraphs = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', is_array($userData['body'] ?? null)
                ? implode("\n", $userData['body'])
                : (string) ($userData['body'] ?? '')) ?: []
        )));
        $letterDate = now()->translatedFormat('F j, Y');
    @endphp

    <div class="professional-header accent-bar" aria-hidden="true"></div>

    <div class="letter-shell">
        <header class="header">
            @if($fullName)
                <h1 class="header-name">{{ $fullName }}</h1>
            @endif
            @if(!empty($userData['jobTitle']))
                <p class="header-title">{{ $userData['jobTitle'] }}</p>
            @endif
            @if(!empty($contactParts))
                <div class="contact-row">
                    @foreach($contactParts as $part)
                        <span>{{ $part }}</span>
                    @endforeach
                </div>
            @endif
        </header>

        <p class="meta-date">{{ $letterDate }}</p>

        @if(!empty($recipientLines))
            <section class="recipient-block">
                @foreach($recipientLines as $index => $line)
                    <p @class(['recipient-name' => $index === 0])>{{ $line }}</p>
                @endforeach
            </section>
        @endif

        @if(!empty($userData['subject']))
            <section class="subject-block">
                <p>{{ __('Subject') }}: {{ $userData['subject'] }}</p>
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
                <div class="signature-rule" aria-hidden="true"></div>
                @if($fullName)
                    <p class="signature">{{ $fullName }}</p>
                @endif
                @if(!empty($userData['jobTitle']))
                    <p class="signature-title">{{ $userData['jobTitle'] }}</p>
                @endif
            </section>
        @endif
    </div>
</x-cover-letter-layout>
