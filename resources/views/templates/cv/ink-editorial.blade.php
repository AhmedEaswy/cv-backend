<x-template-layout :cv="$cv" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Source+Serif+4:wght@400;600&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Source Serif 4', 'Noto Sans Arabic', serif; font-size: 10.5pt; line-height: 1.6; color: #111; }
        .page { width: 210mm; min-height: 297mm; padding: 18mm 20mm; background: #fff; }
        .masthead { display: flex; align-items: flex-end; gap: 18px; margin-bottom: 10px; page-break-inside: avoid; }
        .photo { width: 72px; height: 88px; object-fit: cover; border: 1px solid #111; flex-shrink: 0; }
        .name { font-family: 'Cormorant Garamond', serif; margin: 0; font-size: 34pt; font-weight: 700; letter-spacing: 0.01em; line-height: 0.95; }
        .title { margin: 6px 0 0; font-size: 11pt; font-style: italic; color: #333; }
        .rule { border: none; border-top: 1px solid #111; margin: 10px 0 8px; }
        .rule-thin { border: none; border-top: 0.5px solid #999; margin: 0 0 14px; }
        .contact { font-size: 9pt; text-align: center; color: #333; letter-spacing: 0.02em; }
        .contact span + span::before { content: "  |  "; }
        h2 { font-family: 'Cormorant Garamond', serif; margin: 16px 0 6px; font-size: 14pt; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; text-align: center; }
        .section { page-break-inside: avoid; }
        .entry { margin-bottom: 12px; page-break-inside: avoid; }
        .entry-top { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; }
        .entry-title { margin: 0; font-weight: 600; font-size: 11pt; }
        .meta { margin: 2px 0 4px; font-size: 9.5pt; font-style: italic; color: #444; }
        .dates { font-size: 9pt; color: #555; white-space: nowrap; }
        p { text-align: justify; margin: 0 0 6px; }
        </style>
    @endslot

    @php
        $userData = $cv['user_data'] ?? [];
        $fullName = trim(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? ''));
        $jobTitle = $userData['jobTitle'] ?? '';
        $summary = $userData['summary'] ?? '';
        $skills = $userData['skills'] ?? [];
        $experiences = $userData['experiences'] ?? [];
        $educations = $userData['educations'] ?? [];
        $projects = $userData['projects'] ?? [];
        $languages = $userData['languages'] ?? [];
        $interests = $userData['interests'] ?? [];
        $photo = $userData['photo'] ?? null;
        $contactParts = array_values(array_filter([$userData['email'] ?? null, $userData['phone'] ?? null, $userData['address'] ?? null, $userData['portfolioUrl'] ?? null]));
        $levelNames = [1 => __('Beginner'), 2 => __('Intermediate'), 3 => __('Advanced'), 4 => __('Fluent'), 5 => __('Native')];
    @endphp

    <header class="masthead">
        @if($photo)
            <img class="photo" src="{{ $photo }}" alt="">
        @endif
        <div>
            <h1 class="name">{{ $fullName }}</h1>
            @if($jobTitle)<p class="title">{{ $jobTitle }}</p>@endif
        </div>
    </header>
    <hr class="rule">
    @if(!empty($contactParts))
        <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
    @endif
    <hr class="rule-thin">

    @if($summary)
        <section class="section"><h2>{{ __('Summary') }}</h2><p>{{ $summary }}</p></section>
    @endif

    @if(!empty($experiences))
        <section class="section">
            <h2>{{ __('Work Experience') }}</h2>
            @foreach($experiences as $exp)
                <div class="entry">
                    <div class="entry-top">
                        <h3 class="entry-title">{{ $exp['position'] ?? '' }}</h3>
                        <span class="dates">{{ $exp['from'] ?? '' }}@if(!empty($exp['to']) || !empty($exp['current'])) – {{ !empty($exp['current']) ? __('Present') : ($exp['to'] ?? '') }}@endif</span>
                    </div>
                    <p class="meta">{{ collect([$exp['company'] ?? null, $exp['location'] ?? null])->filter()->implode(' · ') }}</p>
                    @if(!empty($exp['description']))<p>{{ $exp['description'] }}</p>@endif
                </div>
            @endforeach
        </section>
    @endif

    @if(!empty($educations))
        <section class="section">
            <h2>{{ __('Education') }}</h2>
            @foreach($educations as $edu)
                <div class="entry">
                    <div class="entry-top">
                        <h3 class="entry-title">{{ $edu['degree'] ?? '' }}{{ !empty($edu['fieldOfStudy']) ? ' — '.$edu['fieldOfStudy'] : '' }}</h3>
                        <span class="dates">{{ $edu['from'] ?? '' }}@if(!empty($edu['to'])) – {{ $edu['to'] }}@endif</span>
                    </div>
                    <p class="meta">{{ $edu['institution'] ?? '' }}</p>
                </div>
            @endforeach
        </section>
    @endif

    @if(!empty($skills))
        <section class="section"><h2>{{ __('Skills') }}</h2><p>{{ collect($skills)->pluck('name')->filter()->implode(', ') }}</p></section>
    @endif

    @if(!empty($projects))
        <section class="section">
            <h2>{{ __('Projects') }}</h2>
            @foreach($projects as $project)
                <div class="entry">
                    <h3 class="entry-title">{{ $project['title'] ?? '' }}</h3>
                    @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                </div>
            @endforeach
        </section>
    @endif

    @if(!empty($languages))
        <section class="section">
            <h2>{{ __('Languages') }}</h2>
            <p>{{ collect($languages)->map(fn($l) => ($l['name'] ?? '').' ('.$levelNames[$l['proficiencyLevel'] ?? 1].')')->implode(' · ') }}</p>
        </section>
    @endif

    @if(!empty($interests))
        <section class="section"><h2>{{ __('Interests') }}</h2><p>{{ collect($interests)->pluck('name')->filter()->implode(', ') }}</p></section>
    @endif
</x-template-layout>
