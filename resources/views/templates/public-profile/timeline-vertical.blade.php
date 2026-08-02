@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
    $timeline = [];
    foreach ($d->experiences as $exp) {
        $timeline[] = [
            'type' => 'work',
            'title' => $exp['position'] ?? '',
            'subtitle' => $exp['company'] ?? '',
            'range' => PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)),
            'body' => $exp['description'] ?? '',
            'sort' => $exp['from'] ?? '0000',
        ];
    }
    foreach ($d->projects as $project) {
        $timeline[] = [
            'type' => 'project',
            'title' => $project['title'] ?? $project['name'] ?? 'Project',
            'subtitle' => 'Project',
            'range' => PublicProfileTemplateData::dateRange($project['from'] ?? null, $project['to'] ?? null, (bool)($project['current'] ?? false)),
            'body' => $project['description'] ?? '',
            'sort' => $project['from'] ?? '0000',
            'url' => $project['url'] ?? null,
        ];
    }
    usort($timeline, fn ($a, $b) => strcmp($b['sort'], $a['sort']));
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="timeline-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.timeline-body { margin: 0; background: #faf9f7; color: #1a1a1a; font-family: Outfit, sans-serif; }
            .top { text-align: center; padding: 56px 20px 40px; }
            .top h1 { font-size: clamp(2rem, 5vw, 3rem); margin: 0 0 8px; font-weight: 700; }
            .top p { color: #666; margin: 0 0 12px; }
            .spine { max-width: 720px; margin: 0 auto; padding: 20px 20px 80px; position: relative; }
            .spine::before { content: ""; position: absolute; left: 50%; top: 0; bottom: 40px; width: 2px; background: #d4d0c8; transform: translateX(-50%); }
            .node { position: relative; width: calc(50% - 28px); margin-bottom: 36px; background: #fff; border: 1px solid #e7e3da; border-radius: 12px; padding: 18px 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
            .node:nth-child(odd) { margin-left: 0; margin-right: auto; }
            .node:nth-child(even) { margin-left: auto; margin-right: 0; }
            .node::after { content: ""; position: absolute; top: 22px; width: 14px; height: 14px; border-radius: 50%; background: #1a1a1a; border: 3px solid #faf9f7; }
            .node:nth-child(odd)::after { right: -36px; }
            .node:nth-child(even)::after { left: -36px; }
            .badge { display: inline-block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; color: #888; margin-bottom: 6px; }
            .node h3 { margin: 0 0 4px; font-size: 1.05rem; }
            .meta { font-size: 0.85rem; color: #777; margin-bottom: 8px; }
            .about-box { max-width: 560px; margin: 0 auto 40px; text-align: center; color: #444; line-height: 1.7; }
            @media (max-width: 700px) {
                .spine::before { left: 16px; }
                .node { width: calc(100% - 40px); margin-left: 40px !important; margin-right: 0 !important; }
                .node::after { left: -32px !important; right: auto !important; }
            }
        </style>
    @endslot

    <header class="top">
        <h1>{{ $d->fullName }}</h1>
        <p>{{ $d->headline ?? $d->jobTitle }}</p>
        @if($d->about)<p class="about-box">{{ $d->about }}</p>@endif
    </header>

    <div class="spine">
        @forelse($timeline as $item)
            <article class="node">
                <div class="badge">{{ $item['type'] }}</div>
                <h3>
                    @if(!empty($item['url']))
                        <a href="{{ $item['url'] }}" style="color:inherit;text-decoration:none">{{ $item['title'] }}</a>
                    @else
                        {{ $item['title'] }}
                    @endif
                </h3>
                <div class="meta">{{ $item['subtitle'] }}@if($item['range']) · {{ $item['range'] }}@endif</div>
                @if($item['body'])<p>{{ $item['body'] }}</p>@endif
            </article>
        @empty
            <p style="text-align:center;color:#888">No timeline items yet.</p>
        @endforelse
    </div>
</x-public-profile-layout>
