@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="poster-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.poster-body { margin: 0; background: #fff; color: #000; font-family: "Space Grotesk", sans-serif; }
            .hero { display: grid; grid-template-columns: 1.2fr 0.8fr; min-height: 70vh; }
            .hero-text { background: #000; color: #fff; padding: 48px 40px; display: flex; flex-direction: column; justify-content: flex-end; }
            .hero-text h1 { font-family: "Archivo Black", sans-serif; font-size: clamp(3rem, 10vw, 7rem); line-height: 0.9; margin: 0 0 20px; text-transform: uppercase; letter-spacing: -0.03em; }
            .hero-text p { font-size: 1.25rem; max-width: 28ch; margin: 0; }
            .hero-media { background: #ff3b00; position: relative; overflow: hidden; }
            .hero-media img { width: 100%; height: 100%; object-fit: cover; mix-blend-mode: multiply; filter: contrast(1.1); }
            .hero-media .fallback { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: "Archivo Black", sans-serif; font-size: 8rem; color: rgba(0,0,0,0.15); }
            .band { background: #ff3b00; color: #000; padding: 14px 40px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; font-size: 0.85rem; display: flex; flex-wrap: wrap; gap: 16px 32px; }
            .content { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
            .panel { padding: 40px; border-bottom: 4px solid #000; border-right: 4px solid #000; }
            .panel:nth-child(2n) { border-right: none; }
            .panel h2 { font-family: "Archivo Black", sans-serif; font-size: 1.6rem; text-transform: uppercase; margin: 0 0 18px; }
            .panel h3 { margin: 0 0 4px; font-size: 1.1rem; }
            .meta { color: #555; font-size: 0.85rem; margin-bottom: 8px; }
            .cta { display: inline-block; margin-top: 16px; background: #000; color: #fff; padding: 14px 22px; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }
            @media (max-width: 800px) { .hero, .content { grid-template-columns: 1fr; } .panel { border-right: none; } }
        </style>
    @endslot

    <section class="hero">
        <div class="hero-text">
            <h1>{{ $d->fullName }}</h1>
            <p>{{ $d->headline ?? $d->jobTitle }}</p>
        </div>
        <div class="hero-media">
            @if($d->photo)
                <img src="{{ $d->photo }}" alt="{{ $d->fullName }}">
            @else
                <div class="fallback">{{ strtoupper(substr($d->fullName, 0, 1)) }}</div>
            @endif
        </div>
    </section>
    <div class="band">
        @foreach($d->contactParts as $part)<span>{{ $part }}</span>@endforeach
    </div>
    <div class="content">
        @if($d->about)
            <div class="panel">
                <h2>About</h2>
                <p>{{ $d->about }}</p>
                @if($d->cta && !empty($d->cta['url']))
                    <a class="cta" href="{{ $d->cta['url'] }}">{{ $d->cta['label'] ?? 'Hire me' }}</a>
                @endif
            </div>
        @endif
        @if($d->services)
            <div class="panel">
                <h2>Services</h2>
                @foreach($d->services as $service)
                    <h3>{{ $service['title'] ?? '' }}</h3>
                    <p style="margin-bottom:16px">{{ $service['description'] ?? '' }}</p>
                @endforeach
            </div>
        @endif
        @if($d->experiences)
            <div class="panel">
                <h2>Work</h2>
                @foreach($d->experiences as $exp)
                    <h3>{{ $exp['position'] ?? '' }}</h3>
                    <div class="meta">{{ $exp['company'] ?? '' }} · {{ PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)) }}</div>
                    @if(!empty($exp['description']))<p style="margin-bottom:16px">{{ $exp['description'] }}</p>@endif
                @endforeach
            </div>
        @endif
        @if($d->projects)
            <div class="panel">
                <h2>Projects</h2>
                @foreach($d->projects as $project)
                    <h3>{{ $project['title'] ?? $project['name'] ?? '' }}</h3>
                    @if(!empty($project['description']))<p style="margin-bottom:16px">{{ $project['description'] }}</p>@endif
                @endforeach
            </div>
        @endif
    </div>
</x-public-profile-layout>
