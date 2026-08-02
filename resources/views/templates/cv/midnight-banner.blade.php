<x-template-layout :cv="$cv" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Outfit', 'Noto Sans Arabic', sans-serif; font-size: 10.5pt; line-height: 1.55; color: #1f2937; }
        .page { width: 210mm; min-height: 297mm; padding: 0; background: #f8fafc; }
        .banner { background: #0a0a0a; color: #fff; padding: 16mm 18mm 14mm; display: flex; align-items: center; gap: 22px; page-break-inside: avoid; }
        .photo { width: 96px; height: 96px; border-radius: 8px; object-fit: cover; border: 2px solid #c9a227; flex-shrink: 0; }
        .name { margin: 0 0 4px; font-size: 26pt; font-weight: 700; letter-spacing: -0.02em; }
        .title { margin: 0 0 10px; font-size: 12pt; color: #c9a227; font-weight: 500; }
        .gold-line { width: 48px; height: 2px; background: #c9a227; margin-bottom: 10px; }
        .contact { font-size: 9.5pt; color: #cbd5e1; }
        .contact span + span::before { content: "  ·  "; color: #c9a227; }
        .body { padding: 14mm 18mm 16mm; }
        h2 { margin: 0 0 8px; font-size: 11pt; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #0a0a0a; }
        h2::after { content: ""; display: block; width: 28px; height: 2px; background: #c9a227; margin-top: 4px; }
        .section { margin-bottom: 16px; page-break-inside: avoid; }
        .entry { margin-bottom: 12px; page-break-inside: avoid; }
        .entry-top { display: flex; justify-content: space-between; gap: 12px; }
        .entry-title { margin: 0; font-weight: 700; }
        .meta { margin: 2px 0 4px; color: #64748b; font-size: 9.5pt; }
        .dates { font-size: 9pt; color: #94a3b8; white-space: nowrap; }
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

    <header class="banner">
        @if($photo)
            <img class="photo" src="{{ $photo }}" alt="">
        @endif
        <div>
            <h1 class="name">{{ $fullName }}</h1>
            @if($jobTitle)<p class="title">{{ $jobTitle }}</p>@endif
            <div class="gold-line" aria-hidden="true"></div>
            @if(!empty($contactParts))
                <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
            @endif
        </div>
    </header>

    <div class="body">
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
            <section class="section"><h2>{{ __('Skills') }}</h2><p>{{ collect($skills)->pluck('name')->filter()->implode(' · ') }}</p></section>
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
    </div>
</x-template-layout>
