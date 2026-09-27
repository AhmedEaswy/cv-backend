@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="classic-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.classic-body { margin: 0; background: #fff; color: #222; font-family: Lato, sans-serif; font-weight: 300; line-height: 1.7; }
            .wrap { max-width: 700px; margin: 0 auto; padding: 64px 24px 96px; text-align: center; }
            .photo { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; margin-bottom: 24px; border: 1px solid #ddd; }
            h1 { font-family: "Libre Baskerville", serif; font-size: clamp(2rem, 5vw, 2.8rem); margin: 0 0 8px; font-weight: 700; }
            .role { color: #666; font-size: 1.05rem; margin: 0 0 20px; }
            .rule { width: 64px; height: 1px; background: #222; margin: 0 auto 28px; border: none; }
            .about { font-family: "Libre Baskerville", serif; font-style: italic; font-size: 1.15rem; color: #444; margin-bottom: 40px; }
            .links { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px 20px; margin-bottom: 48px; font-size: 0.9rem; }
            .links a { color: #222; }
            .section { text-align: left; margin-bottom: 40px; }
            .section h2 { font-family: "Libre Baskerville", serif; font-size: 1.35rem; text-align: center; margin: 0 0 24px; font-weight: 700; }
            .item { margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #eee; }
            .item:last-child { border-bottom: none; }
            .item h3 { margin: 0 0 4px; font-size: 1rem; font-weight: 700; font-family: Lato, sans-serif; }
            .meta { color: #888; font-size: 0.85rem; margin-bottom: 6px; }
            .skills { text-align: center; }
            .skills span { display: inline-block; margin: 4px 10px; }
            .footer-cta { margin-top: 16px; }
            .footer-cta a { display: inline-block; border: 1px solid #222; padding: 12px 28px; text-decoration: none; color: #222; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; font-size: 0.8rem; }
            .classic-social { margin-bottom: 32px; --pp-social-bg: #fff; --pp-social-border: #ddd; }
            .classic-social .pp-social { justify-content: center; }
            .classic-contact { text-align: left; margin-top: 48px; padding: 28px; border: 1px solid #eee; max-width: 560px; margin-inline: auto; }
            .classic-contact h2 { font-family: "Libre Baskerville", serif; text-align: center; font-size: 1.35rem; margin: 0 0 8px; }
            .classic-contact .cv-contact__form { display: flex; flex-direction: column; gap: 12px; position: relative; }
            .classic-contact .cv-contact__input { width: 100%; padding: 10px 12px; border: 1px solid #ddd; font-family: Lato, sans-serif; }
            .classic-contact .cv-contact__submit { align-self: center; border: 1px solid #222; background: #222; color: #fff; padding: 12px 28px; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.06em; cursor: pointer; }
        </style>
    @endslot

    <div class="wrap">
        @if($d->photo)<img class="photo" src="{{ $d->photo }}" alt="{{ $d->fullName }}">@endif
        <h1>{{ $d->fullName }}</h1>
        <p class="role">{{ $d->headline ?? $d->jobTitle }}</p>
        <hr class="rule">
        @if($d->about)<p class="about">{{ $d->about }}</p>@endif

        <div class="links">
            @foreach($d->contactParts as $part)
                @if(str_contains((string)$part, '@'))
                    <a href="mailto:{{ $part }}">{{ $part }}</a>
                @elseif(str_starts_with((string)$part, 'http'))
                    <a href="{{ $part }}" target="_blank" rel="noopener">Website</a>
                @else
                    <span>{{ $part }}</span>
                @endif
            @endforeach
        </div>
        <div class="classic-social">@include('components.public-profile._social_links', ['links' => $d->socialLinks, 'variant' => 'row'])</div>

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
                        <h3>{{ $project['title'] ?? $project['name'] ?? '' }}</h3>
                        @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                    </div>
                @endforeach
            </section>
        @endif

        @if($d->educations)
            <section class="section">
                <h2>Education</h2>
                @foreach($d->educations as $edu)
                    <div class="item">
                        <h3>{{ $edu['degree'] ?? '' }}</h3>
                        <div class="meta">{{ $edu['institution'] ?? '' }}</div>
                    </div>
                @endforeach
            </section>
        @endif

        @if($d->skills)
            <section class="section skills">
                <h2>Skills</h2>
                @foreach($d->skills as $skill)
                    <span>{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</span>
                @endforeach
            </section>
        @endif

        @if($d->cta && !empty($d->cta['url']))
            <div class="footer-cta">
                <a href="{{ $d->cta['url'] }}">{{ $d->cta['label'] ?? 'Contact' }}</a>
            </div>
        @endif
        @if($profile['enable_contact_form'] ?? false)
            <section id="contact-form" class="classic-contact" aria-labelledby="classic-contact-heading">
                <h2 id="classic-contact-heading">{{ __('messages.public_profile.contact.title') }}</h2>
                <p style="text-align:center;color:#888;margin:0 0 20px">{{ __('messages.public_profile.contact.text') }}</p>
                @include('components.public-profile._contact_fields', ['profile' => $profile])
            </section>
        @endif
    </div>
</x-public-profile-layout>
