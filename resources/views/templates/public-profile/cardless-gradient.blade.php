@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="gradient-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.gradient-body {
                margin: 0; color: #0f172a; font-family: Sora, sans-serif;
                background: linear-gradient(135deg, #67e8f9 0%, #a78bfa 40%, #f472b6 75%, #fb923c 100%);
                background-attachment: fixed; min-height: 100vh;
            }
            .hero {
                min-height: 72vh; display: flex; flex-direction: column; justify-content: flex-end;
                padding: 48px 28px 56px; color: #fff;
                background: linear-gradient(180deg, transparent 20%, rgba(15,23,42,0.55) 100%);
            }
            .hero h1 { font-size: clamp(2.6rem, 7vw, 4.5rem); margin: 0 0 12px; font-weight: 700; letter-spacing: -0.03em; }
            .hero p { font-size: 1.2rem; max-width: 36ch; margin: 0; font-weight: 300; opacity: 0.95; }
            .glass {
                max-width: 1000px; margin: -40px auto 60px; padding: 0 20px;
                display: grid; gap: 16px;
            }
            .panel {
                background: rgba(255,255,255,0.55); backdrop-filter: blur(16px);
                border: 1px solid rgba(255,255,255,0.65); border-radius: 20px; padding: 28px;
            }
            .panel h2 { margin: 0 0 14px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: #475569; }
            .panel h3 { margin: 0 0 4px; font-size: 1.05rem; }
            .meta { color: #64748b; font-size: 0.85rem; margin-bottom: 8px; }
            .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .cta {
                display: inline-block; margin-top: 12px; background: #0f172a; color: #fff;
                text-decoration: none; padding: 12px 22px; border-radius: 999px; font-weight: 600; font-size: 0.9rem;
            }
            .chips { display: flex; flex-wrap: wrap; gap: 8px; }
            .chip { background: rgba(255,255,255,0.7); padding: 8px 14px; border-radius: 999px; font-size: 0.85rem; font-weight: 500; }
            @media (max-width: 700px) { .grid2 { grid-template-columns: 1fr; } }
            .grad-social { padding: 0 28px 16px; --pp-social-color: #fff; --pp-social-bg: rgba(255,255,255,0.15); --pp-social-border: rgba(255,255,255,0.35); }
            .grad-contact.panel { margin-top: 0; }
            .grad-contact .cv-contact__form { display: flex; flex-direction: column; gap: 12px; position: relative; }
            .grad-contact .cv-contact__input { width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.65); font-family: inherit; }
            .grad-contact .cv-contact__submit { background: #0f172a; color: #fff; border: none; padding: 12px 22px; border-radius: 999px; font-weight: 600; cursor: pointer; align-self: flex-start; }
        </style>
    @endslot

    <section class="hero">
        <h1>{{ $d->fullName }}</h1>
        <p>{{ $d->headline ?? $d->jobTitle }}</p>
        <div class="grad-social">@include('components.public-profile._social_links', ['links' => $d->socialLinks, 'variant' => 'pills'])</div>
    </section>

    <div class="glass">
        @if($d->about)
            <div class="panel">
                <h2>About</h2>
                <p>{{ $d->about }}</p>
                @if($d->cta && !empty($d->cta['url']))
                    <a class="cta" href="{{ $d->cta['url'] }}">{{ $d->cta['label'] ?? 'Get in touch' }}</a>
                @endif
            </div>
        @endif

        <div class="grid2">
            @if($d->experiences)
                <div class="panel">
                    <h2>Experience</h2>
                    @foreach($d->experiences as $exp)
                        <div style="margin-bottom:16px">
                            <h3>{{ $exp['position'] ?? '' }}</h3>
                            <div class="meta">{{ $exp['company'] ?? '' }} · {{ PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)) }}</div>
                            @if(!empty($exp['description']))<p>{{ $exp['description'] }}</p>@endif
                        </div>
                    @endforeach
                </div>
            @endif
            @if($d->services)
                <div class="panel">
                    <h2>Services</h2>
                    @foreach($d->services as $service)
                        <div style="margin-bottom:14px">
                            <h3>{{ $service['title'] ?? '' }}</h3>
                            <p>{{ $service['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($d->projects)
            <div class="panel">
                <h2>Projects</h2>
                <div class="grid2">
                    @foreach($d->projects as $project)
                        <div>
                            <h3>{{ $project['title'] ?? $project['name'] ?? '' }}</h3>
                            @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($d->skills)
            <div class="panel">
                <h2>Skills</h2>
                <div class="chips">
                    @foreach($d->skills as $skill)
                        <span class="chip">{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        @if($profile['enable_contact_form'] ?? false)
            <div id="contact-form" class="panel grad-contact" aria-labelledby="grad-contact-heading">
                <h2 id="grad-contact-heading">{{ __('messages.public_profile.contact.title') }}</h2>
                <p style="margin:0 0 16px;color:#475569">{{ __('messages.public_profile.contact.text') }}</p>
                @include('components.public-profile._contact_fields', ['profile' => $profile])
            </div>
        @endif
    </div>
</x-public-profile-layout>
