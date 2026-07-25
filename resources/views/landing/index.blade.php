<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' || app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('landing.meta_title') }} — {{ config('app.name', 'CV') }}</title>
    <meta name="description" content="{{ __('landing.meta_description') }}">

    {{-- Brand & UI fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400;500;600;700&display=swap" rel="stylesheet">

    @if(in_array(app()->getLocale(), ['ar', 'ur']))
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-primary: #5c17e7;
            --brand-primary-soft: #efe7ff;
            --brand-primary-dark: #4a10c0;
            --brand-midnight: #130e21;
            --brand-midnight-soft: #1f1838;
            --ink: #1a1623;
            --ink-soft: #4a4458;
            --muted: #8a8499;
            --paper: #fbfaf7;
            --paper-2: #f4f1ea;
            --paper-line: #e8e3d7;
            --accent-warm: #d97a4a;
            --accent-soft: #fcefe6;
        }

        html, body { background: var(--paper); color: var(--ink); }
        body { font-family: 'Inter', 'Figtree', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        html[dir="rtl"] body { font-family: 'Cairo', 'Inter', system-ui, sans-serif; }

        .font-display { font-family: 'Inter', system-ui, sans-serif; font-feature-settings: "ss01", "cv11"; letter-spacing: -0.02em; }
        html[dir="rtl"] .font-display { font-family: 'Cairo', system-ui, sans-serif; letter-spacing: 0; }

        .hero-wash {
            background:
                radial-gradient(900px 500px at 85% -10%, rgba(92, 23, 231, 0.10), transparent 60%),
                radial-gradient(700px 400px at 5% 10%, rgba(217, 122, 74, 0.08), transparent 60%),
                linear-gradient(180deg, var(--paper) 0%, var(--paper) 60%, var(--paper-2) 100%);
        }
        html[dir="rtl"] .hero-wash {
            background:
                radial-gradient(900px 500px at 15% -10%, rgba(92, 23, 231, 0.10), transparent 60%),
                radial-gradient(700px 400px at 95% 10%, rgba(217, 122, 74, 0.08), transparent 60%),
                linear-gradient(180deg, var(--paper) 0%, var(--paper) 60%, var(--paper-2) 100%);
        }

        /* Soft lavender CTA — matches app primary button (bg #A6A0FF / text #25224A) */
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: .55rem;
            background: #A6A0FF; color: #25224A;
            padding: .9rem 1.75rem; border-radius: 18px;
            font-weight: 600; font-size: .98rem; line-height: 1;
            border: none; box-shadow: none;
            transition: transform .2s ease, background .2s ease, color .2s ease;
        }
        .btn-primary:hover {
            background: #9590f0;
            color: #1c1938;
            transform: translateY(-1px);
        }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: .5rem;
            color: var(--brand-midnight);
            padding: .9rem 1.35rem; border-radius: 18px;
            font-weight: 500; font-size: .98rem; line-height: 1;
            border: 1px solid rgba(19, 14, 33, 0.08);
            background: rgba(255,255,255,0.6);
            transition: background .2s ease, border-color .2s ease;
        }
        .btn-ghost:hover { background: #fff; border-color: rgba(19, 14, 33, 0.18); }

        .store-badges {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: .85rem;
        }
        .store-badge {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            height: 52px;
            padding: 0 22px 0 18px;
            border-radius: 9999px;
            background: #fff;
            color: #130e21;
            border: 1.5px solid #130e21;
            box-sizing: border-box;
            line-height: 1;
            transition: background .15s ease, transform .15s ease, box-shadow .15s ease;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(19, 14, 33, 0.06);
        }
        .store-badge:hover {
            background: #f7f5fb;
            color: #130e21;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(19, 14, 33, 0.1);
        }
        .store-badge-apple { min-width: 168px; }
        .store-badge-google { min-width: 168px; }
        .store-badge-icon {
            height: 26px;
            width: 26px;
            display: block;
            flex-shrink: 0;
        }
        .store-badge-google .store-badge-icon { height: 24px; width: 24px; }
        .store-badge-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            text-align: start;
        }
        .store-badge-caption {
            font-size: 10px;
            font-weight: 400;
            letter-spacing: .01em;
            color: #130e21;
            line-height: 1.1;
        }
        .store-badge-google .store-badge-caption {
            letter-spacing: .06em;
            text-transform: uppercase;
            font-size: 9px;
        }
        .store-badge-label {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -.02em;
            line-height: 1.05;
            white-space: nowrap;
            color: #130e21;
        }
        .store-badge-google .store-badge-label {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -.01em;
        }
        @media (max-width: 480px) {
            .store-badge { height: 48px; padding: 0 18px 0 14px; }
            .store-badge-label { font-size: 15px; }
            .store-badge-google .store-badge-label { font-size: 14px; }
            .store-badge-icon { height: 22px; width: 22px; }
            .store-badge-google .store-badge-icon { height: 20px; width: 20px; }
        }

        /* ============== Templates row carousel (3-up) ============== */
        .tp-carousel { position: relative; }
        .tp-viewport { overflow: hidden; margin: 0 -.25rem; padding: 0 .25rem; }
        .tp-track {
            display: flex;
            gap: 1.25rem;
            transition: transform .55s cubic-bezier(.22, .61, .36, 1);
            will-change: transform;
        }
        html[dir="rtl"] .tp-track { flex-direction: row-reverse; }
        .tp-slide {
            flex: 0 0 100%;
            min-width: 0;
        }
        @media (min-width: 640px) {
            .tp-slide { flex: 0 0 calc((100% - 1.25rem) / 2); }
        }
        @media (min-width: 1024px) {
            .tp-slide { flex: 0 0 calc((100% - 2.5rem) / 3); }
        }
        .tp-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .tp-preview {
            aspect-ratio: 3 / 4;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 1.1rem;
            border: 1px solid rgba(19,14,33,.06);
        }
        .tp-meta { padding: 0 .15rem; }
        .tp-meta h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--brand-midnight);
            margin: 0 0 .45rem;
            letter-spacing: -.02em;
        }
        .tp-meta p {
            margin: .55rem 0 0;
            font-size: .92rem;
            line-height: 1.55;
            color: var(--ink-soft);
        }
        .tp-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .tp-nav-btns { display: flex; gap: .5rem; }
        .tp-btn {
            width: 40px; height: 40px; border-radius: 10px;
            background: #fff; color: var(--brand-midnight);
            border: 1px solid rgba(19,14,33,.1);
            display: inline-flex; align-items: center; justify-content: center;
            transition: background .15s ease, border-color .15s ease;
        }
        .tp-btn:hover:not(:disabled) {
            background: var(--brand-primary-soft);
            border-color: rgba(92,23,231,.25);
        }
        .tp-btn:disabled { opacity: .35; cursor: not-allowed; }
        .tp-dots { display: flex; gap: 6px; align-items: center; }
        .tp-dot {
            width: 8px; height: 8px; border-radius: 999px;
            background: rgba(19,14,33,.18);
            border: 0; padding: 0; cursor: pointer;
            transition: background .2s ease, width .2s ease;
        }
        .tp-dot[aria-current="true"] { background: var(--brand-primary); width: 22px; }

        /* ============== Marquee feature strip ============== */
        .marquee {
            position: relative;
            overflow: hidden;
            padding: 3rem 0;
            background: var(--paper-2);
            border-top: 1px solid rgba(19,14,33,.05);
            border-bottom: 1px solid rgba(19,14,33,.05);
        }
        .marquee::before, .marquee::after {
            content: "";
            position: absolute; top: 0; bottom: 0; width: 80px;
            z-index: 2; pointer-events: none;
        }
        .marquee::before { left: 0;  background: linear-gradient(90deg, var(--paper-2), transparent); }
        .marquee::after  { right: 0; background: linear-gradient(-90deg, var(--paper-2), transparent); }
        html[dir="rtl"] .marquee::before { left: auto; right: 0; background: linear-gradient(-90deg, var(--paper-2), transparent); }
        html[dir="rtl"] .marquee::after  { right: auto; left: 0; background: linear-gradient(90deg, var(--paper-2), transparent); }

        .marquee-track {
            display: flex;
            gap: 1.25rem;
            width: max-content;
            animation: marquee 38s linear infinite;
        }
        .marquee:hover .marquee-track { animation-play-state: paused; }
        html[dir="rtl"] .marquee-track { animation: marquee-rtl 38s linear infinite; }
        @keyframes marquee {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }
        @keyframes marquee-rtl {
            from { transform: translateX(0); }
            to   { transform: translateX(50%); }
        }
        @media (prefers-reduced-motion: reduce) {
            .marquee-track { animation: none; }
        }

        .marquee-card {
            flex: 0 0 300px;
            width: 300px; height: 300px;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 1px 0 rgba(19,14,33,.04), 0 16px 32px -20px rgba(19,14,33,.18);
            border: 1px solid rgba(19,14,33,.05);
            position: relative;
        }
        .marquee-card img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .marquee-card-fallback {
            width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .85rem;
            padding: 1.25rem;
            text-align: center;
            background: linear-gradient(135deg, var(--brand-primary-soft), var(--paper-2));
        }
        .marquee-card-icon {
            width: 56px; height: 56px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 14px;
            background: var(--brand-primary);
            color: #fff;
        }

        .sheet {
            background: #fff;
            border-radius: 6px;
            box-shadow:
                0 1px 0 rgba(19,14,33,.04),
                0 12px 28px -16px rgba(19,14,33,.25),
                0 30px 60px -30px rgba(19,14,33,.35);
            position: relative;
            overflow: hidden;
        }
        .sheet::after {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0) 60%, rgba(19,14,33,0.03) 100%);
            pointer-events: none;
        }
        .sheet-line { background: var(--paper-line); height: 6px; border-radius: 3px; }
        .sheet-line.short { width: 60%; }
        .sheet-line.thin { height: 4px; }
        .sheet-divider { height: 1px; background: rgba(19,14,33,0.06); margin: 10px 0; }
        .chip { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 9px; font-weight: 500; }
        .chip-violet { background: var(--brand-primary-soft); color: var(--brand-primary); }
        .chip-warm { background: var(--accent-soft); color: var(--accent-warm); }
        .chip-mute { background: #f3f1ec; color: #5d5867; }

        .phone {
            width: 220px; aspect-ratio: 9 / 19;
            background: var(--brand-midnight);
            border-radius: 36px;
            padding: 10px;
            box-shadow:
                0 20px 50px -20px rgba(19,14,33,.55),
                0 0 0 1px rgba(255,255,255,0.06) inset;
        }
        .phone-screen {
            background: #fff; border-radius: 28px; width: 100%; height: 100%;
            overflow: hidden; position: relative;
        }
        .phone-notch {
            position: absolute; top: 8px; left: 50%; transform: translateX(-50%);
            width: 70px; height: 18px; background: var(--brand-midnight);
            border-radius: 0 0 12px 12px; z-index: 5;
        }
        html[dir="rtl"] .phone-notch { transform: translateX(50%); }

        .reveal { opacity: 0; transform: translateY(8px); transition: opacity .8s ease, transform .8s ease; }
        .reveal.in { opacity: 1; transform: translateY(0); }

        .rule-soft { height: 1px; background: linear-gradient(90deg, transparent, rgba(19,14,33,0.08), transparent); }

        .step-num {
            width: 32px; height: 32px; border-radius: 999px;
            background: var(--brand-midnight); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: .85rem;
        }

        .surface-card {
            background: #fff; border-radius: 18px;
            box-shadow: 0 1px 0 rgba(19,14,33,0.04), 0 24px 48px -28px rgba(19,14,33,0.25);
            border: 1px solid rgba(19,14,33,0.04);
        }

        footer { background: var(--brand-midnight); color: #cfc8e6; }
        footer a:hover { color: #fff; }

        ::selection { background: rgba(92,23,231,0.18); color: var(--brand-midnight); }

        html { scroll-behavior: smooth; }

        @media (prefers-reduced-motion: reduce) {
            .reveal { opacity: 1; transform: none; transition: none; }
        }

        /* ============== Language dropdown ============== */
        .lang-dropdown { position: relative; }
        .lang-trigger {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .4rem .55rem;
            border-radius: 8px;
            font-size: .875rem; font-weight: 500;
            color: var(--ink-soft);
            background: transparent;
            border: 1px solid transparent;
            transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .lang-trigger:hover {
            background: rgba(19,14,33,0.04);
            color: var(--brand-midnight);
        }
        .lang-trigger[aria-expanded="true"] {
            background: #fff;
            border-color: rgba(19,14,33,0.12);
            color: var(--brand-midnight);
        }
        .lang-trigger .chev { opacity: .55; transition: transform .15s ease; }
        .lang-trigger[aria-expanded="true"] .chev { transform: rotate(180deg); }
        .lang-menu {
            position: absolute; top: calc(100% + 6px);
            inset-inline-end: 0;
            min-width: 10.5rem;
            max-height: 280px; overflow-y: auto;
            background: #fff;
            border: 1px solid rgba(19,14,33,0.1);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(19,14,33,0.1);
            padding: 4px 0;
            z-index: 50;
            opacity: 0; transform: translateY(-2px); pointer-events: none;
            transition: opacity .12s ease, transform .12s ease;
        }
        .lang-menu.open { opacity: 1; transform: translateY(0); pointer-events: auto; }
        .lang-option {
            display: block;
            width: 100%;
            padding: .5rem .85rem;
            font-size: .875rem;
            color: var(--ink);
            text-align: start;
            transition: background .1s ease;
        }
        .lang-option:hover { background: rgba(19,14,33,0.05); }
        .lang-option[aria-current="true"] {
            color: var(--brand-midnight);
            font-weight: 600;
        }

        /* ============== Testimonial slider ============== */
        .slider { position: relative; }
        .slider-viewport { overflow: hidden; }
        .slider-track {
            display: flex;
            gap: 1.25rem;
            transition: transform .5s cubic-bezier(.22, .61, .36, 1);
            will-change: transform;
        }
        html[dir="rtl"] .slider-track { flex-direction: row-reverse; }
        .slider-slide { flex: 0 0 100%; min-width: 0; }
        @media (min-width: 768px) {
            .slider-slide { flex: 0 0 calc((100% - 1.25rem) / 2); }
        }
        @media (min-width: 1024px) {
            .slider-slide { flex: 0 0 calc((100% - 2.5rem) / 3); }
        }
        .slider-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 44px; height: 44px; border-radius: 999px;
            background: #fff; color: var(--brand-midnight);
            border: 1px solid rgba(19,14,33,0.08);
            box-shadow: 0 8px 24px -10px rgba(19,14,33,0.25);
            display: inline-flex; align-items: center; justify-content: center;
            transition: background .2s ease, transform .2s ease, border-color .2s ease;
            z-index: 5;
        }
        .slider-btn:hover { background: var(--brand-primary-soft); border-color: rgba(92,23,231,0.25); }
        .slider-btn:disabled { opacity: .35; cursor: not-allowed; }
        .slider-btn.prev { inset-inline-start: -8px; }
        .slider-btn.next { inset-inline-end: -8px; }
        .slider-dots { display: flex; gap: 6px; justify-content: center; margin-top: 1.5rem; }
        .slider-dot {
            width: 8px; height: 8px; border-radius: 999px;
            background: rgba(255,255,255,0.18);
            border: 0; padding: 0;
            transition: background .2s ease, width .2s ease;
            cursor: pointer;
        }
        .slider-dot[aria-current="true"] { background: #b3a4ff; width: 22px; }

        /* ============== Edges-gate attribution ============== */
        .edges-gate { display: inline-flex; align-items: center; gap: .6rem; }
        .edges-gate img { height: 26px; width: auto; }
    </style>
</head>
<body class="antialiased">

    @php
        $locales = [
            'en' => ['code' => 'EN', 'name' => 'English',   'rtl' => false],
            'ar' => ['code' => 'ع',  'name' => 'العربية',  'rtl' => true ],
            'tr' => ['code' => 'TR', 'name' => 'Türkçe',   'rtl' => false],
            'es' => ['code' => 'ES', 'name' => 'Español',   'rtl' => false],
            'fr' => ['code' => 'FR', 'name' => 'Français',  'rtl' => false],
            'de' => ['code' => 'DE', 'name' => 'Deutsch',   'rtl' => false],
            'ur' => ['code' => 'UR', 'name' => 'اردو',       'rtl' => true ],
        ];
        $currentLocale = app()->getLocale();
        $appName = config('app.name');
        $appStoreUrl = config('services.app_store.url');
        $playStoreUrl = config('services.play_store.url');
    @endphp

    {{-- ============== HEADER ============== --}}
    <header class="sticky top-0 z-40 backdrop-blur-md" style="background: rgba(251, 250, 247, 0.78); border-bottom: 1px solid rgba(19,14,33,0.05);">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 h-16 flex items-center justify-between gap-3">
            <a href="{{ url('/') }}" class="flex items-center" aria-label="{{ $appName }} — home">
                <img src="{{ asset('images/logo-horizontal.png') }}" alt="{{ $appName }}" class="h-7 sm:h-8 w-auto" onerror='this.outerHTML="<span class=&quot;font-display font-bold text-[#130e21] text-lg&quot;>{{ $appName }}</span>"'>
            </a>

            <nav class="hidden md:flex items-center gap-7 text-sm text-[#4a4458]">
                <a href="#templates" class="hover:text-[#130e21] transition">{{ __('landing.nav_templates') }}</a>
                <a href="#how" class="hover:text-[#130e21] transition">{{ __('landing.nav_how') }}</a>
                <a href="#cover" class="hover:text-[#130e21] transition">{{ __('landing.nav_cover') }}</a>
                <a href="#voices" class="hover:text-[#130e21] transition">{{ __('landing.nav_voices') }}</a>
            </nav>

            <div class="flex items-center gap-2">
                {{-- Language dropdown --}}
                <div class="lang-dropdown" data-lang-dropdown>
                    <button type="button"
                            class="lang-trigger"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            aria-label="{{ __('change_language') }}"
                            data-lang-trigger>
                        <span>{{ $locales[$currentLocale]['code'] ?? strtoupper($currentLocale) }}</span>
                        <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <div class="lang-menu" role="listbox" data-lang-menu>
                        @foreach($locales as $code => $meta)
                            <a href="{{ route('landing.locale', $code) }}"
                               class="lang-option"
                               role="option"
                               lang="{{ $code }}"
                               dir="{{ $meta['rtl'] ? 'rtl' : 'ltr' }}"
                               aria-current="{{ $currentLocale === $code ? 'true' : 'false' }}">
                                <span>{{ $meta['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="#download" class="btn-primary">{{ __('landing.nav_cta') }}</a>
            </div>
        </div>
    </header>

    {{-- ============== HERO ============== --}}
    <section class="hero-wash">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 pt-16 pb-20 sm:pt-24 sm:pb-28 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7">

                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-[#130e21] leading-[1.05] mb-5">
                    {{ __('landing.hero_title') }}
                </h1>

                <p class="text-lg text-[#4a4458] leading-relaxed max-w-xl mb-8">
                    {{ __('landing.hero_subtitle') }}
                </p>

                <div class="flex flex-wrap items-center gap-3 mb-8">
                    <a href="#download" class="btn-primary">
                        {{ __('landing.hero_cta_primary') }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                    </a>
                    <a href="#templates" class="btn-ghost">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        {{ __('landing.hero_cta_secondary') }}
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-[#8a8499]">
                    <span class="inline-flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                        {{ __('landing.reassure_1') }}
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                        {{ __('landing.reassure_2') }}
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                        {{ __('landing.reassure_3') }}
                    </span>
                </div>
            </div>

            <div class="lg:col-span-5 relative">
                <div class="relative h-[440px] sm:h-[500px]">
                    <div class="sheet absolute top-2 start-2 w-[210px] sm:w-[230px] p-5 rotate-[-4deg] z-10" style="background: #fffefb;">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 rounded-full" style="background: var(--brand-primary);"></div>
                            <div class="flex-1">
                                <div class="sheet-line short thin"></div>
                            </div>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="sheet-line" style="width: 80%;"></div>
                            <div class="sheet-line" style="width: 65%;"></div>
                        </div>
                        <div class="space-y-1 mb-3">
                            <div class="sheet-line thin" style="width: 95%;"></div>
                            <div class="sheet-line thin" style="width: 92%;"></div>
                            <div class="sheet-line thin" style="width: 88%;"></div>
                            <div class="sheet-line thin" style="width: 90%;"></div>
                            <div class="sheet-line thin" style="width: 70%;"></div>
                        </div>
                        <div class="mt-4">
                            <div class="sheet-line thin" style="width: 50%;"></div>
                        </div>
                        <div class="mt-4 inline-block chip chip-violet">{{ __('landing.mockup_cover_label') }}</div>
                    </div>

                    <div class="sheet absolute top-12 end-0 w-[230px] sm:w-[250px] p-5 rotate-[3deg] z-20">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-midnight));"></div>
                            <div class="flex-1 pt-1">
                                <div class="sheet-line" style="width: 90%;"></div>
                                <div class="sheet-line short thin mt-1.5"></div>
                            </div>
                        </div>

                        <div class="space-y-1 mb-3">
                            <div class="sheet-line thin" style="width: 95%;"></div>
                            <div class="sheet-line thin" style="width: 88%;"></div>
                        </div>

                        <div class="text-[9px] font-semibold uppercase tracking-wider text-[#8a8499] mt-4 mb-2">{{ __('landing.mockup_section_experience') }}</div>
                        <div class="sheet-line" style="width: 70%;"></div>
                        <div class="space-y-1 mt-2 mb-3">
                            <div class="sheet-line thin" style="width: 90%;"></div>
                            <div class="sheet-line thin" style="width: 85%;"></div>
                            <div class="sheet-line thin" style="width: 78%;"></div>
                        </div>

                        <div class="text-[9px] font-semibold uppercase tracking-wider text-[#8a8499] mt-3 mb-2">{{ __('landing.mockup_section_skills') }}</div>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="chip chip-violet">Figma</span>
                            <span class="chip chip-warm">SQL</span>
                            <span class="chip chip-mute">Python</span>
                            <span class="chip chip-violet">REST</span>
                        </div>

                        <div class="mt-4 inline-block chip chip-mute">{{ __('landing.mockup_cv_label') }}</div>
                    </div>

                    <div class="phone absolute bottom-0 end-6 sm:end-10 z-30 rotate-[6deg]">
                        <div class="phone-screen">
                            <div class="phone-notch"></div>
                            <div class="p-3 pt-7 h-full flex flex-col">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-12 h-1.5 rounded-full" style="background: var(--brand-midnight);"></div>
                                    <div class="w-5 h-5 rounded-full" style="background: var(--brand-primary);"></div>
                                </div>

                                <div class="rounded-md p-2 mb-2" style="background: var(--paper-2);">
                                    <div class="w-5 h-5 rounded mb-1.5" style="background: var(--brand-primary);"></div>
                                    <div class="w-full h-1 rounded mb-1" style="background: #d9d4c7;"></div>
                                    <div class="w-3/4 h-1 rounded mb-2" style="background: #d9d4c7;"></div>
                                    <div class="space-y-1">
                                        <div class="w-full h-0.5 rounded" style="background: #e8e3d7;"></div>
                                        <div class="w-5/6 h-0.5 rounded" style="background: #e8e3d7;"></div>
                                        <div class="w-4/6 h-0.5 rounded" style="background: #e8e3d7;"></div>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-3 h-3 rounded-sm" style="background: var(--brand-primary);"></div>
                                        <div class="flex-1 h-1 rounded" style="background: #ece8de;"></div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-3 h-3 rounded-sm" style="background: var(--accent-warm);"></div>
                                        <div class="flex-1 h-1 rounded" style="background: #ece8de;"></div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-3 h-3 rounded-sm" style="background: var(--brand-midnight);"></div>
                                        <div class="flex-1 h-1 rounded" style="background: #ece8de;"></div>
                                    </div>
                                </div>

                                <div class="mt-auto rounded-md py-1.5 text-center text-[9px] font-semibold text-white" style="background: var(--brand-primary);">
                                    PDF
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-6 inset-x-0 h-px rule-soft"></div>
            </div>
        </div>
    </section>

    {{-- ============== MARQUEE FEATURE STRIP ============== --}}
    <section class="marquee" aria-label="{{ __('landing.templates_eyebrow') }}">
        <div class="marquee-track" data-marquee>
            @php
                $marqueeFeatures = [
                    ['n' => 1, 'icon' => 'eye'],
                    ['n' => 2, 'icon' => 'globe'],
                    ['n' => 3, 'icon' => 'check'],
                    ['n' => 4, 'icon' => 'download'],
                    ['n' => 5, 'icon' => 'mail'],
                    ['n' => 6, 'icon' => 'device'],
                ];
            @endphp

            {{-- First copy (originals) --}}
            @foreach($marqueeFeatures as $f)
                <div class="marquee-card">
                    <img src="{{ asset('images/marquee/feature-' . $f['n'] . '.svg') }}"
                         alt="{{ __('landing.marquee_feature_alt_' . $f['n']) }}"
                         loading="lazy"
                         onerror="this.outerHTML='<div class=&quot;marquee-card-fallback&quot;><span class=&quot;marquee-card-icon&quot;>' + thisDatasetIcon('{{ $f['icon'] }}') + '</span><span class=&quot;text-xs font-semibold text-[#130e21]&quot;>{{ __('landing.marquee_feature_alt_' . $f['n']) }}</span></div>'">
                </div>
            @endforeach

            {{-- Duplicate copy (for seamless infinite loop) --}}
            @foreach($marqueeFeatures as $f)
                <div class="marquee-card" aria-hidden="true">
                    <img src="{{ asset('images/marquee/feature-' . $f['n'] . '.svg') }}"
                         alt=""
                         loading="lazy"
                         onerror="this.outerHTML='<div class=&quot;marquee-card-fallback&quot;><span class=&quot;marquee-card-icon&quot;>' + thisDatasetIcon('{{ $f['icon'] }}') + '</span><span class=&quot;text-xs font-semibold text-[#130e21]&quot;>{{ __('landing.marquee_feature_alt_' . $f['n']) }}</span></div>'">
                </div>
            @endforeach
        </div>
    </section>

    <script>
        // Fallback icon SVG by name (for the onerror handler above)
        function thisDatasetIcon(name) {
            const icons = {
                eye:    '<svg width=\"26\" height=\"26\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z\"/><circle cx=\"12\" cy=\"12\" r=\"3\"/></svg>',
                globe:  '<svg width=\"26\" height=\"26\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18\"/></svg>',
                check:  '<svg width=\"26\" height=\"26\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.4\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M20 6 9 17l-5-5\"/></svg>',
                download:'<svg width=\"26\" height=\"26\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 3v12M7 10l5 5 5-5M5 21h14\"/></svg>',
                mail:   '<svg width=\"26\" height=\"26\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><rect x=\"3\" y=\"5\" width=\"18\" height=\"14\" rx=\"2\"/><path d=\"m3 7 9 6 9-6\"/></svg>',
                device: '<svg width=\"26\" height=\"26\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><rect x=\"6\" y=\"2\" width=\"12\" height=\"20\" rx=\"2.5\"/><path d=\"M11 18h2\"/></svg>',
            };
            return icons[name] || icons.eye;
        }
    </script>

    {{-- ============== TEMPLATES (3-up slide row) ============== --}}
    <section id="templates" class="py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-2xl mb-10">
                <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--brand-primary);">{{ __('landing.templates_eyebrow') }}</div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-3 leading-tight">
                    {{ __('landing.templates_title') }}
                </h2>
                <p class="text-[#4a4458] text-lg">
                    {{ __('landing.templates_subtitle') }}
                </p>
            </div>

            @php $templateKeys = ['modern', 'classic', 'minimalist']; @endphp
            <div class="tp-carousel" data-tp-carousel>
                <div class="tp-viewport">
                    <div class="tp-track" data-tp-track>
                        @foreach($templateKeys as $key)
                            @php
                                $name = __("landing.template_{$key}_name");
                                $tag  = __("landing.template_{$key}_tag");
                                $desc = __("landing.template_{$key}_desc");
                            @endphp
                            <article class="tp-slide">
                                <div class="tp-card">
                                    <div class="tp-preview" style="background: {{ $key === 'classic' ? '#f4f1ea' : 'var(--paper-2)' }};">
                                        @if($key === 'modern')
                                            <div class="w-[72%] h-[86%] rounded-sm bg-white shadow-md flex overflow-hidden">
                                                <div class="w-1/3 h-full" style="background: var(--brand-primary);"></div>
                                                <div class="flex-1 p-2.5 space-y-1.5">
                                                    <div class="w-3/4 h-1.5 rounded" style="background: var(--brand-midnight);"></div>
                                                    <div class="w-1/2 h-1 rounded" style="background: #d9d4c7;"></div>
                                                    <div class="w-full h-px my-1.5" style="background: #ece8de;"></div>
                                                    <div class="w-full h-0.5 rounded" style="background: #ece8de;"></div>
                                                    <div class="w-5/6 h-0.5 rounded" style="background: #ece8de;"></div>
                                                    <div class="w-4/6 h-0.5 rounded" style="background: #ece8de;"></div>
                                                    <div class="w-full h-px my-1.5" style="background: #ece8de;"></div>
                                                    <div class="w-full h-0.5 rounded" style="background: #ece8de;"></div>
                                                    <div class="w-3/4 h-0.5 rounded" style="background: #ece8de;"></div>
                                                </div>
                                            </div>
                                        @elseif($key === 'classic')
                                            <div class="w-[72%] h-[86%] rounded-sm bg-white shadow-md p-3.5 flex flex-col">
                                                <div class="text-center mb-2">
                                                    <div class="w-2/3 h-2 rounded mx-auto mb-1" style="background: var(--brand-midnight);"></div>
                                                    <div class="w-1/2 h-1 rounded mx-auto" style="background: #b8b1a3;"></div>
                                                </div>
                                                <div class="w-full h-px my-1" style="background: var(--brand-midnight);"></div>
                                                <div class="w-full h-1 mt-1.5 mb-1" style="background: var(--brand-midnight);"></div>
                                                <div class="w-full h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                                <div class="w-11/12 h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                                <div class="w-4/6 h-0.5 mb-2" style="background: #d9d4c7;"></div>
                                                <div class="w-full h-1 mb-1" style="background: var(--brand-midnight);"></div>
                                                <div class="w-full h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                                <div class="w-5/6 h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                                <div class="w-3/4 h-0.5" style="background: #d9d4c7;"></div>
                                            </div>
                                        @else
                                            <div class="w-[72%] h-[86%] rounded-sm bg-white shadow-md p-3.5 flex flex-col">
                                                <div class="w-1/2 h-2 rounded mb-1" style="background: var(--brand-midnight);"></div>
                                                <div class="w-1/3 h-0.5 rounded mb-3" style="background: var(--brand-primary);"></div>
                                                <div class="w-full h-0.5 mb-0.5" style="background: #ece8de;"></div>
                                                <div class="w-11/12 h-0.5 mb-3" style="background: #ece8de;"></div>
                                                <div class="w-1/4 h-1 mb-1.5" style="background: var(--brand-midnight);"></div>
                                                <div class="w-full h-0.5 mb-0.5" style="background: #ece8de;"></div>
                                                <div class="w-3/4 h-0.5" style="background: #ece8de;"></div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="tp-meta">
                                        <h3 class="font-display">{{ $name }}</h3>
                                        <span class="chip chip-violet">{{ $tag }}</span>
                                        <p>{{ $desc }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="tp-nav">
                    <div class="tp-dots" role="tablist" data-tp-dots>
                        @foreach($templateKeys as $i => $key)
                            <button type="button"
                                    class="tp-dot"
                                    role="tab"
                                    aria-label="{{ __('landing.slider_goto', ['n' => $i + 1]) }}"
                                    aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                                    data-tp-dot="{{ $i }}"></button>
                        @endforeach
                    </div>
                    <div class="tp-nav-btns">
                        <button type="button" class="tp-btn" aria-label="{{ __('landing.slider_prev') }}" data-tp-prev>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="rtl:rotate-180" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                        <button type="button" class="tp-btn" aria-label="{{ __('landing.slider_next') }}" data-tp-next>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="rtl:rotate-180" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============== HOW IT WORKS ============== --}}
    <section id="how" class="py-20 sm:py-28" style="background: var(--paper-2);">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-2xl mb-12">
                <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--brand-primary);">{{ __('landing.how_eyebrow') }}</div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-3 leading-tight">
                    {{ __('landing.how_title') }}
                </h2>
                <p class="text-[#4a4458] text-lg">
                    {{ __('landing.how_subtitle') }}
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @for($i = 1; $i <= 3; $i++)
                    <div class="surface-card p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="step-num">{{ $i }}</span>
                            <div class="h-px flex-1" style="background: linear-gradient(90deg, rgba(19,14,33,0.08), transparent);"></div>
                        </div>
                        <h3 class="font-display font-semibold text-[#130e21] text-xl mb-2">
                            {{ __('landing.how_step_' . $i . '_title') }}
                        </h3>
                        <p class="text-[#4a4458] leading-relaxed">
                            {{ __('landing.how_step_' . $i . '_desc') }}
                        </p>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- ============== COVER LETTER FEATURE ============== --}}
    <section id="cover" class="py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 order-2 lg:order-1">
                <div class="relative max-w-md mx-auto">
                    <div class="sheet p-7 rotate-[-2deg]" style="background: #fffefb;">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-7 h-7 rounded-full" style="background: var(--brand-primary);"></div>
                            <div class="w-7 h-7 rounded-full -ms-2.5" style="background: var(--brand-midnight); border: 2px solid #fffefb;"></div>
                        </div>

                        <div class="mb-4">
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-[#8a8499] mb-1.5">{{ __('landing.cover_label_to') }}</div>
                            <div class="sheet-line" style="width: 55%;"></div>
                        </div>

                        <div class="mb-4">
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-[#8a8499] mb-1.5">{{ __('landing.cover_label_subject') }}</div>
                            <div class="sheet-line" style="width: 75%;"></div>
                        </div>

                        <div class="space-y-1.5 mb-5">
                            <div class="sheet-line thin" style="width: 98%;"></div>
                            <div class="sheet-line thin" style="width: 95%;"></div>
                            <div class="sheet-line thin" style="width: 90%;"></div>
                            <div class="sheet-line thin" style="width: 92%;"></div>
                            <div class="sheet-line thin" style="width: 85%;"></div>
                        </div>

                        <div class="space-y-1.5 mb-5">
                            <div class="sheet-line thin" style="width: 96%;"></div>
                            <div class="sheet-line thin" style="width: 88%;"></div>
                            <div class="sheet-line thin" style="width: 92%;"></div>
                            <div class="sheet-line thin" style="width: 70%;"></div>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <div>
                                <div class="sheet-line thin mb-1" style="width: 70px;"></div>
                                <div class="text-[10px] font-semibold" style="color: var(--brand-primary);">{{ __('landing.cover_label_sincerely') }}</div>
                            </div>
                            <div class="chip chip-violet">{{ __('landing.cover_label_matching') }}</div>
                        </div>
                    </div>

                    <div class="absolute -top-4 -end-4 w-16 h-16 rounded-full flex items-center justify-center shadow-lg" style="background: var(--brand-primary); color: #fff;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6 order-1 lg:order-2">
                <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--brand-primary);">{{ __('landing.cover_eyebrow') }}</div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-4 leading-tight">
                    {{ __('landing.cover_title') }}
                </h2>
                <p class="text-[#4a4458] text-lg leading-relaxed mb-6">
                    {{ __('landing.cover_subtitle') }}
                </p>

                <ul class="space-y-3">
                    @foreach(['match', 'tone', 'export', 'rtl'] as $feat)
                        <li class="flex items-start gap-3">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full mt-0.5 flex-shrink-0" style="background: var(--brand-primary-soft); color: var(--brand-primary);">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="text-[#4a4458] leading-relaxed">{{ __('landing.cover_feat_' . $feat) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ============== TESTIMONIALS (SLIDER) ============== --}}
    @php $slides = [1, 2, 3]; @endphp
    <section id="voices" class="py-20 sm:py-28" style="background: var(--brand-midnight); color: #f5f1ea;">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-2xl mb-12">
                <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: #b3a4ff;">{{ __('landing.voices_eyebrow') }}</div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold mb-3 leading-tight" style="color: #fff;">
                    {{ __('landing.voices_title') }}
                </h2>
                <p class="text-lg" style="color: #cfc8e6;">
                    {{ __('landing.voices_subtitle') }}
                </p>
            </div>

            <div class="slider" data-slider>
                <button type="button"
                        class="slider-btn prev"
                        aria-label="{{ __('landing.slider_prev') }}"
                        data-slider-prev>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="rtl:rotate-180" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>

                <div class="slider-viewport">
                    <div class="slider-track" data-slider-track>
                        @foreach($slides as $i)
                            <figure class="slider-slide rounded-2xl p-6" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="mb-4" style="color: #b3a4ff;" aria-hidden="true"><path d="M9.13 12.74H5.65c.34-2.4 2.26-4.04 4.65-4.55V6.1c-3.95.49-7.4 3.05-7.4 8.1v5.9h7.4v-7.36zm10 0h-3.48c.34-2.4 2.26-4.04 4.65-4.55V6.1c-3.96.49-7.4 3.05-7.4 8.1v5.9h7.4v-7.36z"/></svg>
                                <blockquote class="text-base leading-relaxed mb-5" style="color: #f5f1ea;">
                                    {{ __('landing.voices_' . $i . '_quote') }}
                                </blockquote>
                                <figcaption class="flex items-center gap-3 pt-4" style="border-top: 1px solid rgba(255,255,255,0.08);">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-semibold text-sm" style="background: var(--brand-primary); color: #fff;">
                                        {{ mb_substr(__('landing.voices_' . $i . '_name'), 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm" style="color: #fff;">{{ __('landing.voices_' . $i . '_name') }}</div>
                                        <div class="text-xs" style="color: #b3a4ff;">{{ __('landing.voices_' . $i . '_role') }}</div>
                                    </div>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>

                <button type="button"
                        class="slider-btn next"
                        aria-label="{{ __('landing.slider_next') }}"
                        data-slider-next>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="rtl:rotate-180" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>

                <div class="slider-dots" role="tablist" data-slider-dots>
                    @foreach($slides as $i)
                        <button type="button"
                                class="slider-dot"
                                role="tab"
                                aria-label="{{ __('landing.slider_goto', ['n' => $i]) }}"
                                aria-current="{{ $i === 1 ? 'true' : 'false' }}"
                                data-slider-dot="{{ $i - 1 }}"></button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============== DOWNLOAD CTA ============== --}}
    <section id="download" class="py-24 sm:py-32 hero-wash">
        <div class="max-w-3xl mx-auto px-5 sm:px-8 text-center">
            <h2 class="font-display text-3xl sm:text-5xl font-bold text-[#130e21] mb-5 leading-tight">
                {{ __('landing.final_title') }}
            </h2>
            <p class="text-lg text-[#4a4458] mb-8 max-w-xl mx-auto">
                {{ __('landing.final_subtitle') }}
            </p>
            <div class="store-badges mb-5">
                <a href="{{ $appStoreUrl }}" class="store-badge store-badge-apple" target="_blank" rel="noopener" aria-label="{{ __('landing.store_download_on') }} {{ __('landing.store_app_store') }}" data-track-click="app_store" data-track-page="landing">
                    <img src="{{ asset('images/app-store.svg') }}" alt="" class="store-badge-icon" width="26" height="26" aria-hidden="true">
                    <span class="store-badge-text">
                        <span class="store-badge-caption">{{ __('landing.store_download_on') }}</span>
                        <span class="store-badge-label">{{ __('landing.store_app_store') }}</span>
                    </span>
                </a>
                <a href="{{ $playStoreUrl }}" class="store-badge store-badge-google" target="_blank" rel="noopener" aria-label="{{ __('landing.store_get_it_on') }} {{ __('landing.store_play_store') }}" data-track-click="play_store" data-track-page="landing">
                    <img src="{{ asset('images/google-play.svg') }}" alt="" class="store-badge-icon" width="24" height="24" aria-hidden="true">
                    <span class="store-badge-text">
                        <span class="store-badge-caption">{{ __('landing.store_get_it_on') }}</span>
                        <span class="store-badge-label">{{ __('landing.store_play_store') }}</span>
                    </span>
                </a>
            </div>
            <p class="mt-5 text-sm text-[#8a8499]">{{ __('landing.final_note') }}</p>
        </div>
    </section>

    {{-- ============== FOOTER ============== --}}
    <footer class="pt-16 pb-8">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="grid md:grid-cols-12 gap-10 mb-10">
                <div class="md:col-span-7">
                    <div class="mb-4">
                        <img src="{{ asset('images/logo-horizontal-white.png') }}" alt="{{ $appName }}" class="h-8 sm:h-9 w-auto" onerror='this.outerHTML="<span class=&quot;font-display font-bold text-white text-lg&quot;>{{ $appName }}</span>"'>
                    </div>
                    <p class="text-sm leading-relaxed" style="color: #b3a4ff; max-width: 38ch;">
                        {{ __('landing.footer_tagline') }}
                    </p>
                </div>

                <div class="md:col-span-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wider mb-4" style="color: #fff;">{{ __('landing.footer_product') }}</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="#templates">{{ __('landing.nav_templates') }}</a></li>
                        <li><a href="#cover">{{ __('landing.nav_cover') }}</a></li>
                        <li><a href="#how">{{ __('landing.nav_how') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="rule-soft mb-6" style="background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);"></div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs" style="color: #8a8499;">
                <div>© {{ date('Y') }} {{ config('app.name') }}. {{ __('landing.footer_rights') }}</div>

                <a href="https://edges-gate.com" target="_blank" rel="noopener" class="edges-gate hover:opacity-90 transition" aria-label="{{ __('landing.footer_created_by') }} edges-gate">
                    <span style="color: #cfc8e6;">{{ __('landing.footer_created_by') }}</span>
                    <img src="{{ asset('images/edges-gate.svg') }}" alt="edges-gate" onerror="this.outerHTML='<span style=&quot;color:#b3a4ff;font-weight:600;&quot;>edges-gate</span>'">
                </a>
            </div>
        </div>
    </footer>

    <script>
        // ============== Language dropdown ==============
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-lang-dropdown]').forEach((root) => {
                const trigger = root.querySelector('[data-lang-trigger]');
                const menu = root.querySelector('[data-lang-menu]');
                if (!trigger || !menu) return;

                const open = () => {
                    menu.classList.add('open');
                    trigger.setAttribute('aria-expanded', 'true');
                };
                const close = () => {
                    menu.classList.remove('open');
                    trigger.setAttribute('aria-expanded', 'false');
                };
                const toggle = () => (trigger.getAttribute('aria-expanded') === 'true' ? close() : open());

                trigger.addEventListener('click', (e) => { e.stopPropagation(); toggle(); });
                document.addEventListener('click', (e) => { if (!root.contains(e.target)) close(); });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
            });
        });

        // ============== Testimonial slider ==============
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-slider]').forEach((slider) => {
                const track = slider.querySelector('[data-slider-track]');
                const prev = slider.querySelector('[data-slider-prev]');
                const next = slider.querySelector('[data-slider-next]');
                const dots = Array.from(slider.querySelectorAll('[data-slider-dot]'));
                if (!track) return;

                const slides = track.children;
                let perView = 1;
                const computePerView = () => {
                    if (window.matchMedia('(min-width: 1024px)').matches) perView = 3;
                    else if (window.matchMedia('(min-width: 768px)').matches) perView = 2;
                    else perView = 1;
                };
                computePerView();

                const totalPages = Math.max(1, slides.length - perView + 1);
                let index = 0;

                const isRtl = document.documentElement.dir === 'rtl';

                const update = () => {
                    const slideWidth = slides[0].getBoundingClientRect().width;
                    const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '20');
                    const offset = (slideWidth + gap) * index;
                    track.style.transform = isRtl
                        ? `translateX(${offset}px)`
                        : `translateX(-${offset}px)`;
                    prev.disabled = index === 0;
                    next.disabled = index >= totalPages - 1;
                    dots.forEach((d, i) => d.setAttribute('aria-current', i === index ? 'true' : 'false'));
                };

                prev.addEventListener('click', () => { if (index > 0) { index--; update(); } });
                next.addEventListener('click', () => { if (index < totalPages - 1) { index++; update(); } });
                dots.forEach((d, i) => d.addEventListener('click', () => { index = i; update(); }));

                window.addEventListener('resize', () => {
                    const oldPerView = perView;
                    computePerView();
                    const newTotal = Math.max(1, slides.length - perView + 1);
                    if (index >= newTotal) index = newTotal - 1;
                    update();
                });

                // Touch / swipe support
                let startX = null;
                track.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
                track.addEventListener('touchend', (e) => {
                    if (startX === null) return;
                    const dx = e.changedTouches[0].clientX - startX;
                    const threshold = 40;
                    if (Math.abs(dx) > threshold) {
                        // In RTL, swipe direction is mirrored
                        if (isRtl ? dx > 0 : dx < 0) {
                            if (index < totalPages - 1) { index++; update(); }
                        } else {
                            if (index > 0) { index--; update(); }
                        }
                    }
                    startX = null;
                });

                update();
            });
        });

        // ============== Templates 3-up carousel ==============
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-tp-carousel]').forEach((root) => {
                const track = root.querySelector('[data-tp-track]');
                const prev = root.querySelector('[data-tp-prev]');
                const next = root.querySelector('[data-tp-next]');
                const dotsWrap = root.querySelector('[data-tp-dots]');
                if (!track) return;

                const slides = Array.from(track.children);
                if (!slides.length) return;

                const isRtl = document.documentElement.dir === 'rtl';
                let perView = 1;
                let index = 0;
                let totalPages = 1;

                const computePerView = () => {
                    if (window.matchMedia('(min-width: 1024px)').matches) perView = 3;
                    else if (window.matchMedia('(min-width: 640px)').matches) perView = 2;
                    else perView = 1;
                    totalPages = Math.max(1, slides.length - perView + 1);
                    if (index >= totalPages) index = totalPages - 1;
                };

                const rebuildDots = () => {
                    if (!dotsWrap) return;
                    dotsWrap.innerHTML = '';
                    for (let i = 0; i < totalPages; i++) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'tp-dot';
                        btn.setAttribute('role', 'tab');
                        btn.setAttribute('aria-label', String(i + 1));
                        btn.setAttribute('aria-current', i === index ? 'true' : 'false');
                        btn.addEventListener('click', () => { index = i; update(); });
                        dotsWrap.appendChild(btn);
                    }
                };

                const update = () => {
                    const slideWidth = slides[0].getBoundingClientRect().width;
                    const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '20') || 20;
                    const offset = (slideWidth + gap) * index;
                    track.style.transform = isRtl
                        ? `translateX(${offset}px)`
                        : `translateX(-${offset}px)`;

                    if (prev) prev.disabled = index === 0 || totalPages <= 1;
                    if (next) next.disabled = index >= totalPages - 1 || totalPages <= 1;

                    if (dotsWrap) {
                        Array.from(dotsWrap.children).forEach((d, i) => {
                            d.setAttribute('aria-current', i === index ? 'true' : 'false');
                        });
                    }
                };

                if (prev) prev.addEventListener('click', () => { if (index > 0) { index--; update(); } });
                if (next) next.addEventListener('click', () => { if (index < totalPages - 1) { index++; update(); } });

                let startX = null;
                track.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
                track.addEventListener('touchend', (e) => {
                    if (startX === null) return;
                    const dx = e.changedTouches[0].clientX - startX;
                    if (Math.abs(dx) > 40) {
                        if (isRtl ? dx > 0 : dx < 0) {
                            if (index < totalPages - 1) { index++; update(); }
                        } else if (index > 0) {
                            index--; update();
                        }
                    }
                    startX = null;
                });

                window.addEventListener('resize', () => {
                    computePerView();
                    rebuildDots();
                    update();
                });

                computePerView();
                rebuildDots();
                update();
            });
        });

        // ============== Reveal on scroll ==============
        document.addEventListener('DOMContentLoaded', () => {
            const els = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window) || !els.length) {
                els.forEach(el => el.classList.add('in'));
                return;
            }
            const io = new IntersectionObserver((entries) => {
                entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
            }, { threshold: 0.08 });
            els.forEach(el => io.observe(el));
        });

        // ============== App download click tracking ==============
        // Fire-and-forget ping when a user clicks App Store / Play Store.
        // Uses sendBeacon so the request survives the page navigating away
        // to the store. Falls back to fetch keepalive when sendBeacon is
        // unavailable.
        (function () {
            const ENDPOINT = @json(url('/api/v1/analytics/click'));

            function send(target, page) {
                const payload = JSON.stringify({ target: target, page: page });
                let blob = null;
                try { blob = new Blob([payload], { type: 'application/json' }); } catch (e) { /* IE — skip */ }

                if (blob && navigator.sendBeacon) {
                    navigator.sendBeacon(ENDPOINT, blob);
                    return;
                }

                try {
                    fetch(ENDPOINT, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: payload,
                        keepalive: true,
                    });
                } catch (e) { /* best-effort, never block the click */ }
            }

            document.addEventListener('click', function (event) {
                const link = event.target.closest && event.target.closest('[data-track-click]');
                if (!link) return;
                const target = link.getAttribute('data-track-click');
                const page = link.getAttribute('data-track-page') || 'landing';
                if (target) send(target, page);
            }, { capture: true });
        })();
    </script>
</body>
</html>
