@php
    $direction = session('direction', 'ltr');
    $locale = app()->getLocale();
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if($locale === 'ar')
        <!-- Arabic Font -->
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
        </style>
    @endif

    @filamentStyles
    @vite(['resources/css/filament/admin/theme.css'])
</head>
<body class="fi-body">
    {{ $slot }}

    @filamentScripts
</body>
</html>

