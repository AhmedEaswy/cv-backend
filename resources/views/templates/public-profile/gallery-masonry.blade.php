@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="gallery-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.gallery-body { margin: 0; background: #111; color: #f5f5f5; font-family: Inter, sans-serif; }
            .bar { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; padding: 28px 28px 20px; border-bottom: 1px solid #2a2a2a; flex-wrap: wrap; }
            .bar h1 { font-family: "Playfair Display", serif; font-size: 1.8rem; margin: 0; }
            .bar .meta { color: #999; font-size: 0.9rem; max-width: 420px; }
            .masonry { column-count: 3; column-gap: 16px; padding: 20px 28px 60px; }
            .tile { break-inside: avoid; margin-bottom: 16px; background: #1a1a1a; border-radius: 6px; overflow: hidden; }
            .tile img { width: 100%; display: block; aspect-ratio: 4/3; object-fit: cover; }
            .tile .ph { aspect-ratio: 4/3; background: linear-gradient(135deg, #2a2a2a, #3d3d3d); display: flex; align-items: center; justify-content: center; color: #666; font-size: 0.85rem; }
            .tile .body { padding: 14px 16px 18px; }
            .tile h3 { margin: 0 0 6px; font-size: 1rem; font-weight: 600; }
            .tile p { margin: 0; color: #aaa; font-size: 0.88rem; line-height: 1.5; }
            .bio { padding: 0 28px 40px; max-width: 640px; color: #bbb; line-height: 1.7; }
            .bio a { color: #fff; }
            @media (max-width: 900px) { .masonry { column-count: 2; } }
            @media (max-width: 560px) { .masonry { column-count: 1; padding: 16px; } .bar { padding: 20px 16px; } }
        </style>
    @endslot

    <header class="bar">
        <div>
            <h1>{{ $d->fullName }}</h1>
            <div style="color:#888;margin-top:4px">{{ $d->jobTitle ?? $d->headline }}</div>
        </div>
        <div class="meta">{{ $d->headline }}</div>
    </header>

    @if($d->about)
        <p class="bio">{{ $d->about }}
            @if($d->email) · <a href="mailto:{{ $d->email }}">{{ $d->email }}</a>@endif
        </p>
    @endif

    <div class="masonry">
        @forelse($d->projects as $project)
            <article class="tile">
                @if(!empty($project['image']))
                    <img src="{{ $project['image'] }}" alt="{{ $project['title'] ?? '' }}">
                @else
                    <div class="ph">{{ $project['title'] ?? 'Project' }}</div>
                @endif
                <div class="body">
                    <h3>
                        @if(!empty($project['url']))
                            <a href="{{ $project['url'] }}" style="color:inherit;text-decoration:none">{{ $project['title'] ?? $project['name'] ?? 'Project' }}</a>
                        @else
                            {{ $project['title'] ?? $project['name'] ?? 'Project' }}
                        @endif
                    </h3>
                    @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                    @if(!empty($project['technologies']))
                        <p style="margin-top:8px;color:#777">{{ implode(' · ', (array)$project['technologies']) }}</p>
                    @endif
                </div>
            </article>
        @empty
            @foreach($d->experiences as $exp)
                <article class="tile">
                    <div class="ph">{{ $exp['company'] ?? 'Work' }}</div>
                    <div class="body">
                        <h3>{{ $exp['position'] ?? '' }}</h3>
                        <p>{{ $exp['description'] ?? '' }}</p>
                    </div>
                </article>
            @endforeach
        @endforelse
    </div>
</x-public-profile-layout>
