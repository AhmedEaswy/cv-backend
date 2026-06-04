@props(['cv'])
<!DOCTYPE html>
<html lang="{{ $cv['language'] ?? 'en' }}" dir="{{ in_array($cv['language'] ?? 'en', ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($cv['user_data']['firstName'] ?? '') . ' ' . ($cv['user_data']['lastName'] ?? '') }} - CV</title>

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