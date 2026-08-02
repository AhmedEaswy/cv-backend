<x-template-layout :cv="$cv" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Nunito Sans', 'Noto Sans Arabic', sans-serif; font-size: 10.5pt; line-height: 1.55; color: #3d405b; }
        .page { width: 210mm; min-height: 297mm; padding: 0; background: #f4f1de; display: flex; flex-direction: column; }
        .split { display: flex; min-height: 48mm; page-break-inside: avoid; }
        .panel { width: 55%; background: #e07a5f; color: #fff; padding: 14mm 12mm; }
        .photo-col { width: 45%; background: #3d405b; display: flex; align-items: center; justify-content: center; padding: 10mm; }
        .photo { width: 120px; height: 120px; border-radius: 16px; object-fit: cover; border: 4px solid #f4f1de; }
        .name { margin: 0 0 6px; font-size: 24pt; font-weight: 700; line-height: 1.15; }
        .title { margin: 0 0 12px; font-size: 11pt; opacity: 0.95; font-weight: 600; }
        .contact { font-size: 9pt; opacity: 0.9; }
        .contact p { margin: 0 0 3px; }
        .body { padding: 12mm 16mm 16mm; }
        h2 { margin: 0 0 8px; font-size: 11pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #e07a5f; }
        .section { margin-bottom: 14px; page-break-inside: avoid; }
        .entry { margin-bottom: 10px; page-break-inside: avoid; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; }
        .entry-title { margin: 0; font-weight: 700; color: #3d405b; }
        .meta { margin: 2px 0 4px; font-size: 9.5pt; color: #81b29a; }
        .dates { font-size: 9pt; color: #81b29a; white-space: nowrap; }
        [dir="rtl"] .split { flex-direction: row-reverse; }
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
        $levelNames = [1 => __('Beginner'), 2 => __('Intermediate'), 3 => __('Advanced'), 4 => __('Fluent'), 5 => __('Native')];
    @endphp

    <header class="split">
        <div class="panel">
            <h1 class="name">{{ $fullName }}</h1>
            @if($jobTitle)<p class="title">{{ $jobTitle }}</p>@endif
            <div class="contact">
                @foreach(array_filter([$userData['email'] ?? null, $userData['phone'] ?? null, $userData['address'] ?? null, $userData['portfolioUrl'] ?? null]) as $c)
                    <p>{{ $c }}</p>
                @endforeach
            </div>
        </div>
        <div class="photo-col">
            @if($photo)
                <img class="photo" src="{{ $photo }}" alt="">
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
