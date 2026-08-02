@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
    $nav = array_filter([
        'about' => $d->about,
        'experience' => $d->experiences,
        'projects' => $d->projects,
        'skills' => $d->skills,
        'education' => $d->educations,
        'contact' => $d->contactParts,
    ]);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="corp-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.corp-body { margin: 0; font-family: "DM Sans", sans-serif; color: #0f172a; background: #f1f5f9; }
            .layout { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
            .rail { background: #0b1f3a; color: #cbd5e1; padding: 32px 22px; position: sticky; top: 0; height: 100vh; }
            .rail h1 { color: #fff; font-size: 1.25rem; margin: 0 0 6px; }
            .rail .role { font-size: 0.85rem; color: #94a3b8; margin-bottom: 28px; }
            .rail nav a { display: block; color: #94a3b8; text-decoration: none; padding: 10px 0; border-bottom: 1px solid rgba(148,163,184,0.15); font-size: 0.9rem; font-weight: 500; }
            .rail nav a:hover { color: #fff; }
            .main { padding: 40px 48px 80px; max-width: 900px; }
            .main section { margin-bottom: 48px; scroll-margin-top: 24px; }
            .main h2 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.14em; color: #64748b; margin: 0 0 16px; }
            .main h3 { margin: 0 0 4px; font-size: 1.05rem; }
            .lead { font-size: 1.2rem; line-height: 1.65; color: #334155; }
            .card-row { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 12px; }
            .meta { color: #64748b; font-size: 0.85rem; margin-bottom: 6px; }
            .chips { display: flex; flex-wrap: wrap; gap: 8px; }
            .chip { background: #e2e8f0; color: #0f172a; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; }
            .photo { width: 72px; height: 72px; border-radius: 8px; object-fit: cover; margin-bottom: 16px; }
            @media (max-width: 800px) { .layout { grid-template-columns: 1fr; } .rail { position: relative; height: auto; } }
        </style>
    @endslot

    <div class="layout">
        <aside class="rail">
            @if($d->photo)<img class="photo" src="{{ $d->photo }}" alt="">@endif
            <h1>{{ $d->fullName }}</h1>
            <div class="role">{{ $d->jobTitle ?? $d->headline }}</div>
            <nav>
                @foreach($nav as $id => $_)
                    <a href="#{{ $id }}">{{ ucfirst($id) }}</a>
                @endforeach
            </nav>
        </aside>
        <main class="main">
            @if($d->about)
                <section id="about">
                    <h2>About</h2>
                    <p class="lead">{{ $d->about }}</p>
                    @if($d->availability)
                        <p class="meta" style="margin-top:12px">Status: {{ $d->availability['status'] ?? '' }}{{ !empty($d->availability['message']) ? ' — '.$d->availability['message'] : '' }}</p>
                    @endif
                </section>
            @endif
            @if($d->experiences)
                <section id="experience">
                    <h2>Experience</h2>
                    @foreach($d->experiences as $exp)
                        <div class="card-row">
                            <h3>{{ $exp['position'] ?? '' }}</h3>
                            <div class="meta">{{ $exp['company'] ?? '' }} · {{ PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)) }}</div>
                            @if(!empty($exp['description']))<p>{{ $exp['description'] }}</p>@endif
                        </div>
                    @endforeach
                </section>
            @endif
            @if($d->projects)
                <section id="projects">
                    <h2>Projects</h2>
                    @foreach($d->projects as $project)
                        <div class="card-row">
                            <h3>{{ $project['title'] ?? $project['name'] ?? '' }}</h3>
                            @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                        </div>
                    @endforeach
                </section>
            @endif
            @if($d->skills)
                <section id="skills">
                    <h2>Skills</h2>
                    <div class="chips">
                        @foreach($d->skills as $skill)
                            <span class="chip">{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
            @if($d->educations)
                <section id="education">
                    <h2>Education</h2>
                    @foreach($d->educations as $edu)
                        <div class="card-row">
                            <h3>{{ $edu['degree'] ?? '' }}{{ !empty($edu['fieldOfStudy']) ? ' in '.$edu['fieldOfStudy'] : '' }}</h3>
                            <div class="meta">{{ $edu['institution'] ?? '' }}</div>
                        </div>
                    @endforeach
                </section>
            @endif
            @if($d->contactParts)
                <section id="contact">
                    <h2>Contact</h2>
                    @foreach($d->contactParts as $part)<p>{{ $part }}</p>@endforeach
                    @if($d->cta && !empty($d->cta['url']))
                        <p><a href="{{ $d->cta['url'] }}" style="color:#0b1f3a;font-weight:600">{{ $d->cta['label'] ?? 'Contact' }}</a></p>
                    @endif
                </section>
            @endif
        </main>
    </div>
</x-public-profile-layout>
