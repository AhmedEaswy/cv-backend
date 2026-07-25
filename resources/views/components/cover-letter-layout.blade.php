@props(['coverLetter'])
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
</head>
<body {{ $attributes->merge(['class' => '']) }}>
    <div class="page">
        {{ $slot }}
    </div>
</body>
</html>
