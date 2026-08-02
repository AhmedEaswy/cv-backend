@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="term-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.term-body { margin: 0; background: #0a0e0a; color: #9fe870; font-family: "IBM Plex Mono", monospace; font-size: 14px; line-height: 1.65; }
            .shell { max-width: 860px; margin: 0 auto; padding: 40px 20px 80px; }
            .prompt { color: #4ade80; margin-bottom: 4px; }
            .prompt span { color: #64748b; }
            .title { font-size: 1.6rem; color: #ecfdf5; margin: 0 0 8px; }
            .sub { color: #86efac; margin: 0 0 28px; }
            .block { border: 1px solid #1f3d2a; background: #0d140f; padding: 18px 20px; margin-bottom: 18px; }
            .block h2 { margin: 0 0 14px; font-size: 0.85rem; color: #4ade80; }
            .block h2::before { content: "> "; }
            .row { margin-bottom: 14px; }
            .row strong { color: #ecfdf5; font-weight: 600; }
            .muted { color: #64748b; }
            a { color: #4ade80; }
            .tags { display: flex; flex-wrap: wrap; gap: 8px; }
            .tag { border: 1px solid #1f3d2a; padding: 4px 10px; color: #86efac; }
            .blink { animation: blink 1.1s step-end infinite; }
            @keyframes blink { 50% { opacity: 0; } }
        </style>
    @endslot

    <div class="shell">
        <div class="prompt">guest@portfolio<span>:~$</span> whoami<span class="blink">_</span></div>
        <h1 class="title">{{ $d->fullName }}</h1>
        <p class="sub">{{ $d->headline ?? $d->jobTitle }} @if($d->location())// {{ $d->location() }}@endif</p>

        @if($d->about)
            <div class="block">
                <h2>about.md</h2>
                <p>{{ $d->about }}</p>
            </div>
        @endif

        @if($d->experiences)
            <div class="block">
                <h2>experience.log</h2>
                @foreach($d->experiences as $exp)
                    <div class="row">
                        <strong>{{ $exp['position'] ?? '' }}</strong> @ {{ $exp['company'] ?? '' }}<br>
                        <span class="muted">{{ PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)) }}</span>
                        @if(!empty($exp['description']))<div>{{ $exp['description'] }}</div>@endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($d->projects)
            <div class="block">
                <h2>projects/</h2>
                @foreach($d->projects as $project)
                    <div class="row">
                        <strong>
                            @if(!empty($project['url']))
                                <a href="{{ $project['url'] }}">{{ $project['title'] ?? 'project' }}</a>
                            @else
                                {{ $project['title'] ?? 'project' }}
                            @endif
                        </strong>
                        @if(!empty($project['description']))<div>{{ $project['description'] }}</div>@endif
                        @if(!empty($project['technologies']))
                            <div class="muted">[{{ implode(', ', (array)$project['technologies']) }}]</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($d->skills)
            <div class="block">
                <h2>skills.json</h2>
                <div class="tags">
                    @foreach($d->skills as $skill)
                        <span class="tag">{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if($d->socialLinks)
            <div class="block">
                <h2>links</h2>
                @foreach($d->socialLinks as $key => $url)
                    @if($url)<div><span class="muted">{{ is_int($key) ? ($url['platform'] ?? 'link') : $key }}:</span>
                        <a href="{{ is_array($url) ? ($url['url'] ?? '#') : $url }}">{{ is_array($url) ? ($url['url'] ?? '') : $url }}</a>
                    </div>@endif
                @endforeach
            </div>
        @endif
    </div>
</x-public-profile-layout>
