@php
    use App\Support\PublicProfileTemplateData;
    $d = PublicProfileTemplateData::from($profile);
@endphp
<x-public-profile-layout :profile="$profile" :preview="$preview ?? false" class="editorial-body">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@300;400;600&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body.editorial-body {
                margin: 0; background: #f3eee4; color: #1c1917;
                font-family: "Source Sans 3", sans-serif; font-weight: 300;
                background-image: radial-gradient(rgba(28,25,23,0.04) 1px, transparent 1px);
                background-size: 18px 18px;
            }
            .masthead { max-width: 1100px; margin: 0 auto; padding: 56px 28px 24px; border-bottom: 2px solid #1c1917; display: grid; grid-template-columns: 1.4fr 1fr; gap: 32px; align-items: end; }
            .masthead h1 { font-family: "Cormorant Garamond", serif; font-size: clamp(3rem, 8vw, 5.5rem); font-weight: 400; line-height: 0.95; margin: 0; letter-spacing: -0.02em; }
            .masthead .deck { font-size: 1.15rem; line-height: 1.5; margin: 0; }
            .grid { max-width: 1100px; margin: 0 auto; padding: 40px 28px 80px; display: grid; grid-template-columns: 2fr 1fr; gap: 48px; }
            .col h2 { font-family: "Cormorant Garamond", serif; font-size: 2rem; font-weight: 600; margin: 0 0 20px; border-top: 1px solid #1c1917; padding-top: 12px; }
            .story { font-size: 1.2rem; line-height: 1.7; }
            .item { margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid #d6d3d1; }
            .item h3 { font-family: "Cormorant Garamond", serif; font-size: 1.45rem; margin: 0 0 4px; font-weight: 600; }
            .meta { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: #78716c; margin-bottom: 8px; }
            .side p, .side li { font-size: 0.95rem; }
            .side ul { list-style: none; padding: 0; margin: 0; }
            .side li { padding: 6px 0; border-bottom: 1px dotted #a8a29e; }
            @media (max-width: 800px) { .masthead, .grid { grid-template-columns: 1fr; } }
            .edit-social { grid-column: 1 / -1; margin-top: 8px; --pp-social-border: #1c1917; --pp-social-bg: transparent; }
            .edit-contact { max-width: 1100px; margin: 0 auto; padding: 0 28px 80px; }
            .edit-contact .panel { border-top: 2px solid #1c1917; padding-top: 24px; }
            .edit-contact h2 { font-family: "Cormorant Garamond", serif; font-size: 2rem; margin: 0 0 12px; }
            .edit-contact .cv-contact__form { display: flex; flex-direction: column; gap: 14px; position: relative; max-width: 520px; }
            .edit-contact .cv-contact__input { width: 100%; padding: 10px 12px; border: 1px solid #1c1917; background: #f3eee4; font-family: inherit; }
            .edit-contact .cv-contact__submit { background: #1c1917; color: #f3eee4; border: none; padding: 12px 20px; font-weight: 600; cursor: pointer; align-self: flex-start; }
        </style>
    @endslot

    <header class="masthead">
        <h1>{{ $d->fullName }}</h1>
        <p class="deck">{{ $d->headline ?? $d->jobTitle }}@if($d->about)<br><em style="font-family:'Cormorant Garamond',serif;font-size:1.25rem">{{ \Illuminate\Support\Str::limit($d->about, 140) }}</em>@endif</p>
        <div class="edit-social">@include('components.public-profile._social_links', ['links' => $d->socialLinks, 'variant' => 'stack'])</div>
    </header>

    <div class="grid">
        <div class="col">
            @if($d->about)
                <h2>Profile</h2>
                <p class="story">{{ $d->about }}</p>
            @endif
            @if($d->experiences)
                <h2 style="margin-top:48px">Career</h2>
                @foreach($d->experiences as $exp)
                    <article class="item">
                        <h3>{{ $exp['position'] ?? '' }}</h3>
                        <div class="meta">{{ $exp['company'] ?? '' }} · {{ PublicProfileTemplateData::dateRange($exp['from'] ?? null, $exp['to'] ?? null, (bool)($exp['current'] ?? false)) }}</div>
                        @if(!empty($exp['description']))<p>{{ $exp['description'] }}</p>@endif
                    </article>
                @endforeach
            @endif
            @if($d->projects)
                <h2>Selected Work</h2>
                @foreach($d->projects as $project)
                    <article class="item">
                        <h3>{{ $project['title'] ?? $project['name'] ?? 'Project' }}</h3>
                        @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                    </article>
                @endforeach
            @endif
        </div>
        <aside class="col side">
            @if($d->contactParts)
                <h2>Contact</h2>
                @foreach($d->contactParts as $part)<p>{{ $part }}</p>@endforeach
            @endif
            @if($d->socialLinks)
                <h2>Connect</h2>
                @include('components.public-profile._social_links', ['links' => $d->socialLinks, 'variant' => 'mono'])
            @endif
            @if($d->skills)
                <h2>Expertise</h2>
                <ul>@foreach($d->skills as $skill)<li>{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</li>@endforeach</ul>
            @endif
            @if($d->educations)
                <h2>Education</h2>
                @foreach($d->educations as $edu)
                    <p><strong>{{ $edu['degree'] ?? '' }}</strong><br>{{ $edu['institution'] ?? '' }}</p>
                @endforeach
            @endif
            @if($d->testimonials)
                <h2>Voices</h2>
                @foreach($d->testimonials as $t)
                    <p style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.2rem">“{{ $t['quote'] ?? '' }}”</p>
                    <p class="meta">{{ $t['author'] ?? '' }}{{ !empty($t['role']) ? ', '.$t['role'] : '' }}</p>
                @endforeach
            @endif
        </aside>
    </div>
    @if($profile['enable_contact_form'] ?? false)
        <section id="contact-form" class="edit-contact" aria-labelledby="edit-contact-heading">
            <div class="panel">
                <h2 id="edit-contact-heading">{{ __('messages.public_profile.contact.title') }}</h2>
                <p style="margin:0 0 18px;color:#78716c">{{ __('messages.public_profile.contact.text') }}</p>
                @include('components.public-profile._contact_fields', ['profile' => $profile])
            </div>
        </section>
    @endif
</x-public-profile-layout>
