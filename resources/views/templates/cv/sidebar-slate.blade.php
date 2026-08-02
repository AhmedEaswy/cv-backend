<x-template-layout :cv="$cv" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Source Sans 3', 'Noto Sans Arabic', sans-serif; font-size: 10pt; line-height: 1.5; color: #1e293b; }
        .page { width: 210mm; min-height: 297mm; padding: 0; background: #fff; display: flex; }
        .sidebar { width: 68mm; background: #1e293b; color: #e2e8f0; padding: 14mm 10mm; flex-shrink: 0; }
        .main { flex: 1; padding: 14mm 12mm; }
        .photo-wrap { text-align: center; margin-bottom: 16px; }
        .photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #94a3b8; }
        .side-name { margin: 0 0 4px; font-size: 16pt; font-weight: 700; color: #fff; text-align: center; line-height: 1.2; }
        .side-title { margin: 0 0 18px; font-size: 9.5pt; color: #94a3b8; text-align: center; }
        .side-h { margin: 16px 0 8px; font-size: 9pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; border-bottom: 1px solid #334155; padding-bottom: 4px; }
        .side-item { margin: 0 0 6px; font-size: 9pt; word-break: break-word; }
        .skill-pill { display: inline-block; margin: 0 4px 4px 0; padding: 2px 8px; background: #334155; border-radius: 4px; font-size: 8.5pt; }
        h1 { margin: 0 0 6px; font-size: 22pt; font-weight: 700; color: #0f172a; }
        .main-title { margin: 0 0 14px; font-size: 12pt; color: #475569; font-weight: 600; }
        h2 { margin: 18px 0 8px; font-size: 11pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #1e293b; border-bottom: 2px solid #1e293b; padding-bottom: 3px; }
        .section { page-break-inside: avoid; }
        .entry { margin-bottom: 12px; page-break-inside: avoid; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; }
        .entry-title { margin: 0; font-weight: 700; font-size: 10.5pt; }
        .dates { font-size: 9pt; color: #64748b; white-space: nowrap; }
        .meta { margin: 2px 0 4px; color: #64748b; font-size: 9.5pt; }
        [dir="rtl"] .page { flex-direction: row-reverse; }
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

    <aside class="sidebar">
        @if($photo)
            <div class="photo-wrap"><img class="photo" src="{{ $photo }}" alt=""></div>
        @endif
        <h1 class="side-name">{{ $fullName }}</h1>
        @if($jobTitle)<p class="side-title">{{ $jobTitle }}</p>@endif

        <h2 class="side-h">{{ __('Contact') }}</h2>
        @foreach(array_filter([$userData['email'] ?? null, $userData['phone'] ?? null, $userData['address'] ?? null, $userData['portfolioUrl'] ?? null]) as $c)
            <p class="side-item">{{ $c }}</p>
        @endforeach

        @if(!empty($skills))
            <h2 class="side-h">{{ __('Skills') }}</h2>
            <div>@foreach($skills as $skill)<span class="skill-pill">{{ $skill['name'] ?? '' }}</span>@endforeach</div>
        @endif

        @if(!empty($languages))
            <h2 class="side-h">{{ __('Languages') }}</h2>
            @foreach($languages as $lang)
                <p class="side-item">{{ $lang['name'] ?? '' }} — {{ $levelNames[$lang['proficiencyLevel'] ?? 1] }}</p>
            @endforeach
        @endif

        @if(!empty($interests))
            <h2 class="side-h">{{ __('Interests') }}</h2>
            <p class="side-item">{{ collect($interests)->pluck('name')->filter()->implode(', ') }}</p>
        @endif
    </aside>

    <div class="main">
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
    </div>
</x-template-layout>
