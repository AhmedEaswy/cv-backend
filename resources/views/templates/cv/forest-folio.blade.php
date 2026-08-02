<x-template-layout :cv="$cv" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Source Sans 3', 'Noto Sans Arabic', sans-serif; font-size: 10pt; line-height: 1.55; color: #1a2e24; }
        .page { width: 210mm; min-height: 297mm; padding: 0; background: #f7f3eb; display: flex; }
        .rail { width: 62mm; background: #2d4a3e; color: #e8efe9; padding: 14mm 9mm; flex-shrink: 0; }
        .content { flex: 1; padding: 14mm 12mm; }
        .photo { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 4px; margin-bottom: 14px; border: 2px solid #a7c4b5; }
        .rail-name { font-family: 'Lora', serif; margin: 0 0 4px; font-size: 15pt; font-weight: 700; color: #fff; line-height: 1.2; }
        .rail-title { margin: 0 0 16px; font-size: 9.5pt; color: #a7c4b5; }
        .rail-h { margin: 14px 0 6px; font-family: 'Lora', serif; font-size: 10pt; color: #a7c4b5; border-bottom: 1px solid #3f5f50; padding-bottom: 3px; }
        .rail-item { margin: 0 0 5px; font-size: 8.5pt; word-break: break-word; }
        h1 { font-family: 'Lora', serif; margin: 0 0 4px; font-size: 22pt; color: #2d4a3e; }
        .lead { margin: 0 0 14px; color: #5a6f63; font-size: 11pt; }
        h2 { font-family: 'Lora', serif; margin: 16px 0 8px; font-size: 12pt; color: #2d4a3e; border-bottom: 1px solid #c5d5cb; padding-bottom: 3px; }
        .section { page-break-inside: avoid; }
        .entry { margin-bottom: 11px; page-break-inside: avoid; }
        .entry-title { margin: 0; font-weight: 700; font-size: 10.5pt; }
        .meta { margin: 2px 0 4px; font-size: 9pt; color: #5a6f63; }
        .dates { font-size: 8.5pt; color: #7a9084; }
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

    <aside class="rail">
        @if($photo)
            <img class="photo" src="{{ $photo }}" alt="">
        @endif
        <p class="rail-name">{{ $fullName }}</p>
        @if($jobTitle)<p class="rail-title">{{ $jobTitle }}</p>@endif

        <h2 class="rail-h">{{ __('Contact') }}</h2>
        @foreach(array_filter([$userData['email'] ?? null, $userData['phone'] ?? null, $userData['address'] ?? null, $userData['portfolioUrl'] ?? null]) as $c)
            <p class="rail-item">{{ $c }}</p>
        @endforeach

        @if(!empty($skills))
            <h2 class="rail-h">{{ __('Skills') }}</h2>
            @foreach($skills as $skill)
                <p class="rail-item">{{ $skill['name'] ?? '' }}</p>
            @endforeach
        @endif

        @if(!empty($languages))
            <h2 class="rail-h">{{ __('Languages') }}</h2>
            @foreach($languages as $lang)
                <p class="rail-item">{{ $lang['name'] ?? '' }} — {{ $levelNames[$lang['proficiencyLevel'] ?? 1] }}</p>
            @endforeach
        @endif

        @if(!empty($interests))
            <h2 class="rail-h">{{ __('Interests') }}</h2>
            <p class="rail-item">{{ collect($interests)->pluck('name')->filter()->implode(', ') }}</p>
        @endif
    </aside>

    <div class="content">
        @if($summary)
            <section class="section">
                <h2>{{ __('Summary') }}</h2>
                <p>{{ $summary }}</p>
            </section>
        @endif

        @if(!empty($experiences))
            <section class="section">
                <h2>{{ __('Work Experience') }}</h2>
                @foreach($experiences as $exp)
                    <div class="entry">
                        <h3 class="entry-title">{{ $exp['position'] ?? '' }}</h3>
                        <p class="meta">{{ collect([$exp['company'] ?? null, $exp['location'] ?? null])->filter()->implode(' · ') }}</p>
                        <p class="dates">{{ $exp['from'] ?? '' }}@if(!empty($exp['to']) || !empty($exp['current'])) – {{ !empty($exp['current']) ? __('Present') : ($exp['to'] ?? '') }}@endif</p>
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
                        <h3 class="entry-title">{{ $edu['degree'] ?? '' }}{{ !empty($edu['fieldOfStudy']) ? ' — '.$edu['fieldOfStudy'] : '' }}</h3>
                        <p class="meta">{{ $edu['institution'] ?? '' }}</p>
                        <p class="dates">{{ $edu['from'] ?? '' }}@if(!empty($edu['to'])) – {{ $edu['to'] }}@endif</p>
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
