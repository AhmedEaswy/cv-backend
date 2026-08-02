@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="pastel-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.pastel-body { margin: 0; font-family: Nunito, sans-serif; color: #3d2c4a; background: linear-gradient(160deg, #fde8f0 0%, #e8f4ff 45%, #f3ffe8 100%); min-height: 100vh; }
            .wrap { max-width: 920px; margin: 0 auto; padding: 48px 20px 80px; }
            .hero { text-align: center; padding: 40px 20px 48px; }
            .hero img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 8px 24px rgba(61,44,74,0.12); margin-bottom: 18px; }
            .hero h1 { font-family: Fraunces, serif; font-size: clamp(2.2rem, 5vw, 3.2rem); margin: 0 0 8px; }
            .hero .tagline { font-size: 1.1rem; color: #7a6588; margin: 0 0 20px; }
            .pills { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
            .pill { background: rgba(255,255,255,0.7); border-radius: 999px; padding: 8px 16px; font-size: 0.9rem; }
            .band { background: rgba(255,255,255,0.55); border-radius: 28px; padding: 32px; margin-bottom: 24px; }
            .band h2 { font-family: Fraunces, serif; margin: 0 0 16px; font-size: 1.6rem; }
            .band-rose { background: rgba(253, 200, 216, 0.45); }
            .band-mint { background: rgba(200, 240, 210, 0.45); }
            .band-sky { background: rgba(190, 220, 255, 0.45); }
            .item { margin-bottom: 18px; }
            .item h3 { margin: 0 0 4px; font-size: 1.05rem; }
            .meta { color: #7a6588; font-size: 0.88rem; margin-bottom: 6px; }
            .skills { display: flex; flex-wrap: wrap; gap: 10px; }
            .skill { background: #fff; border-radius: 16px; padding: 10px 16px; font-weight: 600; font-size: 0.9rem; }
            .cta { display: inline-block; background: #3d2c4a; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 999px; font-weight: 700; margin-top: 8px; }
        </style>
    @endslot

    <div class="wrap">
        <header class="hero">
            @if($d->photo)<img src="{{ $d->photo }}" alt="{{ $d->fullName }}">@endif
            <h1>{{ $d->fullName }}</h1>
            <p class="tagline">{{ $d->headline ?? $d->jobTitle }}</p>
            <div class="pills">
                @foreach($d->contactParts as $part)<span class="pill">{{ $part }}</span>@endforeach
            </div>
        </header>

        @if($d->about)
            <section class="band band-rose">
                <h2>Hello</h2>
                <p>{{ $d->about }}</p>
                @if($d->cta && !empty($d->cta['url']))
                    <a class="cta" href="{{ $d->cta['url'] }}">{{ $d->cta['label'] ?? 'Say hi' }}</a>
                @endif
            </section>
        @endif

        @if($d->services)
            <section class="band band-mint">
                <h2>What I offer</h2>
                @foreach($d->services as $service)
                    <div class="item">
                        <h3>{{ $service['title'] ?? '' }}</h3>
                        <p>{{ $service['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </section>
        @endif

        @if($d->experiences)
            <section class="band band-sky">
                <h2>Journey</h2>
                @foreach($d->experiences as $exp)
                    <div class="item">
                        <h3>{{ $exp['position'] ?? '' }} · {{ $exp['company'] ?? '' }}</h3>
                        <div class="meta">{{ PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)) }}</div>
                        @if(!empty($exp['description']))<p>{{ $exp['description'] }}</p>@endif
                    </div>
                @endforeach
            </section>
        @endif

        @if($d->skills)
            <section class="band">
                <h2>Skills</h2>
                <div class="skills">
                    @foreach($d->skills as $skill)
                        <span class="skill">{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if($d->testimonials)
            <section class="band band-rose">
                <h2>Kind words</h2>
                @foreach($d->testimonials as $t)
                    <div class="item">
                        <p style="font-family:Fraunces,serif;font-size:1.2rem">“{{ $t['quote'] ?? '' }}”</p>
                        <div class="meta">— {{ $t['author'] ?? '' }}{{ !empty($t['company']) ? ', '.$t['company'] : '' }}</div>
                    </div>
                @endforeach
            </section>
        @endif
    </div>
</x-public-profile-layout>
