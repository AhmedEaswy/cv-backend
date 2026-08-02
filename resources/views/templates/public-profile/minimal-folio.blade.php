@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
        <style>
            :root { --ink: #111; --muted: #6b6b6b; --line: #e8e8e8; --bg: #fafafa; }
            * { box-sizing: border-box; }
            body { margin: 0; background: var(--bg); color: var(--ink); font-family: "Instrument Sans", system-ui, sans-serif; font-weight: 300; line-height: 1.7; }
            .wrap { max-width: 680px; margin: 0 auto; padding: 72px 24px 96px; }
            .name { font-size: clamp(2.4rem, 6vw, 3.4rem); font-weight: 400; letter-spacing: -0.03em; margin: 0 0 8px; line-height: 1.1; }
            .role { color: var(--muted); font-size: 1.05rem; margin: 0 0 28px; }
            .photo { width: 88px; height: 88px; border-radius: 50%; object-fit: cover; margin-bottom: 28px; filter: grayscale(1); }
            .about { font-size: 1.15rem; margin-bottom: 48px; }
            .section { margin-bottom: 48px; }
            .section h2 { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.18em; color: var(--muted); font-weight: 500; margin: 0 0 20px; padding-bottom: 10px; border-bottom: 1px solid var(--line); }
            .item { margin-bottom: 22px; }
            .item h3 { margin: 0 0 4px; font-size: 1rem; font-weight: 500; }
            .meta { color: var(--muted); font-size: 0.88rem; margin-bottom: 6px; }
            .skills { display: flex; flex-wrap: wrap; gap: 8px 18px; list-style: none; padding: 0; margin: 0; }
            .skills li { font-size: 0.95rem; }
            .contact { display: flex; flex-wrap: wrap; gap: 8px 20px; font-size: 0.9rem; color: var(--muted); margin-bottom: 40px; }
            .contact a { color: inherit; text-decoration: underline; text-underline-offset: 3px; }
            .cta { display: inline-block; margin-top: 8px; color: var(--ink); border-bottom: 1px solid var(--ink); text-decoration: none; padding-bottom: 2px; font-weight: 500; }
        </style>
    @endslot

    <main class="wrap">
        @if($d->photo)<img class="photo" src="{{ $d->photo }}" alt="{{ $d->fullName }}">@endif
        <h1 class="name">{{ $d->fullName }}</h1>
        <p class="role">{{ $d->headline ?? $d->jobTitle }}</p>
        @if($d->contactParts)
            <div class="contact">
                @foreach($d->contactParts as $part)
                    @if(str_contains($part, '@'))
                        <a href="mailto:{{ $part }}">{{ $part }}</a>
                    @elseif(str_starts_with($part, 'http'))
                        <a href="{{ $part }}" target="_blank" rel="noopener">{{ $part }}</a>
                    @else
                        <span>{{ $part }}</span>
                    @endif
                @endforeach
            </div>
        @endif
        @if($d->about)<p class="about">{{ $d->about }}</p>@endif

        @if($d->experiences)
            <section class="section">
                <h2>Experience</h2>
                @foreach($d->experiences as $exp)
                    <div class="item">
                        <h3>{{ $exp['position'] ?? '' }} — {{ $exp['company'] ?? '' }}</h3>
                        <div class="meta">{{ PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)) }}</div>
                        @if(!empty($exp['description']))<p>{{ $exp['description'] }}</p>@endif
                    </div>
                @endforeach
            </section>
        @endif

        @if($d->projects)
            <section class="section">
                <h2>Projects</h2>
                @foreach($d->projects as $project)
                    <div class="item">
                        <h3>
                            @if(!empty($project['url']))
                                <a href="{{ $project['url'] }}" style="color:inherit">{{ $project['title'] ?? $project['name'] ?? 'Project' }}</a>
                            @else
                                {{ $project['title'] ?? $project['name'] ?? 'Project' }}
                            @endif
                        </h3>
                        @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                    </div>
                @endforeach
            </section>
        @endif

        @if($d->skills)
            <section class="section">
                <h2>Skills</h2>
                <ul class="skills">
                    @foreach($d->skills as $skill)
                        <li>{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($d->cta && !empty($d->cta['url']))
            <a class="cta" href="{{ $d->cta['url'] }}">{{ $d->cta['label'] ?? 'Get in touch' }}</a>
        @endif
    </main>
</x-public-profile-layout>
