@props(['coverLetter', 'preview' => false])
<!DOCTYPE html>
<html lang="{{ $coverLetter['language'] ?? 'en' }}" dir="{{ in_array($coverLetter['language'] ?? 'en', ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($coverLetter['user_data']['firstName'] ?? '') . ' ' . ($coverLetter['user_data']['lastName'] ?? '') }} - {{ __('Cover Letter') }}</title>

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
            body.preview-canvas.preview-embed {
                padding: 8px !important;
                background: #eef0f3 !important;
            }
            body.preview-canvas.preview-embed .page {
                box-shadow: none;
            }
        </style>
    @endif
</head>
<body {{ $attributes->class([
    'preview-canvas' => $preview,
    'preview-embed' => $preview && request()->boolean('embed'),
]) }}>
    <div class="page">
        {{ $slot }}
    </div>
</body>
</html>
