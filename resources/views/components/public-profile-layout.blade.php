@props(['profile', 'preview' => false])
@php
    $seo = $profile['user_data']['seo'] ?? [];
    $title = $seo['meta_title']
        ?? trim(($profile['user_data']['firstName'] ?? '') . ' ' . ($profile['user_data']['lastName'] ?? ''))
        ?: 'Public Profile';
    $description = $seo['meta_description'] ?? ($profile['headline'] ?? $profile['about'] ?? '');
    $canonical = $profile['public_url'] ?? url()->current();
    $robots = $seo['robots'] ?? 'index,follow';
    $ogDescription = \Illuminate\Support\Str::limit(strip_tags((string) $description), 160);
    $lang = $profile['language'] ?? 'en';
    $dir = in_array($lang, ['ar', 'ur']) ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @if($description)
        <meta name="description" content="{{ $ogDescription }}">
    @endif
    @if($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="profile">
    <meta property="og:title" content="{{ $title }}">
    @if($ogDescription)
        <meta property="og:description" content="{{ $ogDescription }}">
    @endif
    <meta property="og:url" content="{{ $canonical }}">
    @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif

    @include('components.partials.bilingual-fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        a,
        a:visited {
            color: inherit;
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        a.cta, .cta, .footer-cta a, .rail nav a {
            text-decoration: none;
        }
    </style>

    @isset($head)
        {{ $head }}
    @endisset

    @include('components.partials.arabic-font-overrides')
</head>
<body {{ $attributes }}>
    {{ $slot }}
</body>
</html>
