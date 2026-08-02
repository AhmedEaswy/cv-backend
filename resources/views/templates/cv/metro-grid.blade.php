<x-template-layout :cv="$cv" :preview="$preview ?? false">
    @slot('head')
        <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;600;800&display=swap" rel="stylesheet">
        <style>
        @page { margin: 0; size: A4; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Libre Franklin', 'Noto Sans Arabic', sans-serif; font-size: 10pt; line-height: 1.5; color: #1c1917; }
        .page { width: 210mm; min-height: 297mm; padding: 14mm 16mm; background: #fff; }
        .masthead { display: grid; grid-template-columns: 110px 1fr; gap: 18px; margin-bottom: 18px; align-items: center; page-break-inside: avoid; }
        .photo { width: 110px; height: 130px; object-fit: cover; border-radius: 2px; }
        .photo-fallback { width: 110px; height: 130px; background: #292524; }
        .name { margin: 0 0 4px; font-size: 28pt; font-weight: 800; letter-spacing: -0.03em; line-height: 1.05; color: #1c1917; }
        .title { margin: 0 0 10px; font-size: 12pt; font-weight: 600; color: #b45309; }
        .contact { font-size: 9pt; color: #57534e; }
        .contact span + span::before { content: "  /  "; color: #b45309; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; }
        .span-2 { grid-column: 1 / -1; }
        h2 { margin: 0 0 8px; font-size: 10pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #b45309; border-top: 3px solid #1c1917; padding-top: 6px; }
        .section { page-break-inside: avoid; }
        .entry { margin-bottom: 10px; page-break-inside: avoid; }
        .entry-title { margin: 0; font-weight: 700; font-size: 10.5pt; }
        .meta { margin: 2px 0; font-size: 9pt; color: #78716c; }
        .dates { font-size: 8.5pt; color: #a8a29e; }
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
        @else
            <div class="photo-fallback" aria-hidden="true"></div>
        @endif
        <div>
            <h1 class="name">{{ $fullName }}</h1>
            @if($jobTitle)<p class="title">{{ $jobTitle }}</p>@endif
            @if(!empty($contactParts))
                <p class="contact">@foreach($contactParts as $part)<span>{{ $part }}</span>@endforeach</p>
            @endif
        </div>
    </header>

    <div class="grid">
        @if($summary)
            <section class="section span-2"><h2>{{ __('Summary') }}</h2><p>{{ $summary }}</p></section>
        @endif

        @if(!empty($experiences))
            <section class="section span-2">
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
                        <h3 class="entry-title">{{ $edu['degree'] ?? '' }}</h3>
                        <p class="meta">{{ $edu['institution'] ?? '' }}{{ !empty($edu['fieldOfStudy']) ? ' · '.$edu['fieldOfStudy'] : '' }}</p>
                        <p class="dates">{{ $edu['from'] ?? '' }}@if(!empty($edu['to'])) – {{ $edu['to'] }}@endif</p>
                    </div>
                @endforeach
            </section>
        @endif

        @if(!empty($skills))
            <section class="section">
                <h2>{{ __('Skills') }}</h2>
                <p>{{ collect($skills)->pluck('name')->filter()->implode(' · ') }}</p>
            </section>
        @endif

        @if(!empty($projects))
            <section class="section span-2">
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
    </div>
</x-template-layout>
