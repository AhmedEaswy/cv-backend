<x-cover-letter-layout :coverLetter="$coverLetter" :preview="$preview ?? false">
    @slot('head')
        <style>
        @page {
            margin: 0;
            size: A4;
        }
        html {
            -webkit-print-color-adjust: exact;
        }
        body {
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 22mm;
            background: #fff;
        }
        p {
            margin: 0 0 6px;
        }
        .sender-block {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }
        .recipient-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .subject-block {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .body-block {
            margin-bottom: 24px;
        }
        .body-block p {
            margin-bottom: 12px;
        }
        .closing-block {
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .signature {
            margin-top: 28px;
            font-weight: 600;
        }
        .signature-title {
            font-size: 10.5pt;
        }
        </style>
    @endslot

    @include('templates.cover-letter._ats-classic-content', ['coverLetter' => $coverLetter])
</x-cover-letter-layout>
