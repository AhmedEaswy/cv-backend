<x-template-layout :cv="$cv" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'DM Sans', 'Noto Sans Arabic', sans-serif; color: #0f172a; font-size: 10.5pt; line-height: 1.55; }
        .page { width: 210mm; min-height: 297mm; padding: 16mm 18mm; background: #fff; }
        .accent { color: #0d9488; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 3px solid #0d9488; page-break-inside: avoid; }
        .header-text { flex: 1; }
        .name { margin: 0 0 4px; font-size: 26pt; font-weight: 700; letter-spacing: -0.02em; line-height: 1.15; }
        .title { margin: 0 0 10px; font-size: 12pt; font-weight: 500; color: #0d9488; }
        .contact { font-size: 9.5pt; color: #475569; }
        .contact span + span::before { content: " · "; color: #94a3b8; }
        .photo { width: 92px; height: 92px; border-radius: 50%; object-fit: cover; border: 3px solid #0d9488; flex-shrink: 0; }
        h2 { margin: 0 0 8px; font-size: 11pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #0d9488; }
        .section { margin-bottom: 16px; page-break-inside: avoid; }
        .entry { margin-bottom: 12px; page-break-inside: avoid; }
        .entry-top { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; }
        .entry-title { margin: 0; font-size: 11pt; font-weight: 700; }
        .entry-meta { margin: 2px 0 4px; font-size: 9.5pt; color: #64748b; }
        .dates { font-size: 9pt; color: #64748b; white-space: nowrap; }
        .skills { display: flex; flex-wrap: wrap; gap: 6px; }
        .skill { padding: 3px 10px; background: #f0fdfa; color: #0f766e; border-radius: 999px; font-size: 9pt; font-weight: 500; }
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

    <header class="header">
        <div class="header-text">
            <h1 class="name">{{ $fullName }}</h1>
            @if($jobTitle)<p class="title">{{ $jobTitle }}</p>@endif
            @if(!empty($contactParts))
                <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
            @endif
        </div>
        @if($photo)
            <img class="photo" src="{{ $photo }}" alt="">
        @endif
    </header>

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
                    <p class="entry-meta">{{ collect([$exp['company'] ?? null, $exp['location'] ?? null])->filter()->implode(' · ') }}</p>
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
                    <p class="entry-meta">{{ $edu['institution'] ?? '' }}</p>
                    @if(!empty($edu['description']))<p>{{ $edu['description'] }}</p>@endif
                </div>
            @endforeach
        </section>
    @endif

    @if(!empty($skills))
        <section class="section">
            <h2>{{ __('Skills') }}</h2>
            <div class="skills">@foreach($skills as $skill)<span class="skill">{{ $skill['name'] ?? '' }}</span>@endforeach</div>
        </section>
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
        <section class="section">
            <h2>{{ __('Interests') }}</h2>
            <p>{{ collect($interests)->pluck('name')->filter()->implode(', ') }}</p>
        </section>
    @endif
</x-template-layout>
