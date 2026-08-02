@props(['cv', 'preview' => false])
<!DOCTYPE html>
<html lang="{{ $cv['language'] ?? 'en' }}" dir="{{ in_array($cv['language'] ?? 'en', ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($cv['user_data']['firstName'] ?? '') . ' ' . ($cv['user_data']['lastName'] ?? '') }} - CV</title>

    @include('components.partials.bilingual-fonts')

    @isset($head)
        {{ $head }}
    @endisset

    @if($preview)
        <style>
            html, body {
                min-height: 100%;
            }
            body.preview-canvas {
                margin: 0 !important;
                background: #f3f4f6 !important;
                display: flex !important;
                justify-content: center !important;
                align-items: flex-start !important;
                padding: 32px 16px !important;
                box-sizing: border-box !important;
            }
            body.preview-canvas .page {
                margin: 0 auto !important;
                border: 1px solid #d1d5db !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
                box-sizing: border-box;
            }
        </style>
    @endif
</head>
<body {{ $attributes->merge(['class' => $preview ? 'preview-canvas' : '']) }}>
    <div class="page">
        {{ $slot }}
    </div>
</body>
</html>
