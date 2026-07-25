<x-template-layout :cv="$cv">
    @slot('head')
        <style>
        @page {
            margin: 0;
            size: A4;
        }
        html {
            -webkit-print-color-adjust: exact;
        }
        body {
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 20mm;
            background: #fff;
        }
        h1 {
            font-size: 20pt;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: 0.02em;
        }
        h2 {
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid #000;
            margin: 18px 0 8px;
            padding-bottom: 3px;
            page-break-after: avoid;
        }
        h3 {
            font-size: 11pt;
            font-weight: 600;
            margin: 0;
        }
        p {
            margin: 0 0 8px;
        }
        a {
            color: #000;
            text-decoration: none;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .job-title {
            font-size: 12pt;
            margin-bottom: 8px;
        }
        .contact-line {
            font-size: 10pt;
        }
        .section {
            page-break-inside: avoid;
        }
        .entry {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
        }
        .entry-dates {
            font-size: 10pt;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .entry-subtitle {
            font-size: 10.5pt;
            margin-top: 2px;
        }
        .skills-list,
        .interests-list {
            margin: 0;
        }
        .language-row {
            margin-bottom: 4px;
        }
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
        $contactParts = array_filter([
            $userData['email'] ?? null,
            $userData['phone'] ?? null,
            $userData['address'] ?? null,
            $userData['portfolioUrl'] ?? null,
        ]);
        $levelNames = [
            1 => __('Beginner'),
            2 => __('Intermediate'),
            3 => __('Advanced'),
            4 => __('Fluent'),
            5 => __('Native'),
        ];
    @endphp

    <header class="header">
        <h1>{{ $fullName }}</h1>
        @if($jobTitle)
            <p class="job-title">{{ $jobTitle }}</p>
        @endif
        @if(!empty($contactParts))
            <p class="contact-line">{{ implode(' | ', $contactParts) }}</p>
        @endif
    </header>

    @if($summary)
        <section class="section">
            <h2>{{ __('Summary') }}</h2>
            <p>{{ $summary }}</p>
        </section>
    @endif

    @if(!empty($skills))
        <section class="section">
            <h2>{{ __('Skills') }}</h2>
            <p class="skills-list">
                {{ collect($skills)->pluck('name')->filter()->implode(', ') }}
            </p>
        </section>
    @endif

    @if(!empty($experiences))
        <section class="section">
            <h2>{{ __('Work Experience') }}</h2>
            @foreach($experiences as $exp)
                <article class="entry">
                    <div class="entry-header">
                        <div>
                            <h3>{{ $exp['position'] ?? '' }}</h3>
                            @if(!empty($exp['company']))
                                <p class="entry-subtitle">{{ $exp['company'] }}@if(!empty($exp['location'])), {{ $exp['location'] }}@endif</p>
                            @endif
                        </div>
                        <p class="entry-dates">
                            @if(!empty($exp['from']))
                                {{ date('M Y', strtotime($exp['from'] . '-01')) }}
                            @endif
                            @if(!empty($exp['from']) || !empty($exp['to']) || ($exp['current'] ?? false))
                                –
                            @endif
                            @if($exp['current'] ?? false)
                                {{ __('Present') }}
                            @elseif(!empty($exp['to']))
                                {{ date('M Y', strtotime($exp['to'] . '-01')) }}
                            @endif
                        </p>
                    </div>
                    @if(!empty($exp['description']))
                        <p>{{ $exp['description'] }}</p>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    @if(!empty($educations))
        <section class="section">
            <h2>{{ __('Education') }}</h2>
            @foreach($educations as $edu)
                <article class="entry">
                    <div class="entry-header">
                        <div>
                            <h3>{{ $edu['degree'] ?? '' }}@if(!empty($edu['fieldOfStudy'])), {{ $edu['fieldOfStudy'] }}@endif</h3>
                            @if(!empty($edu['institution']))
                                <p class="entry-subtitle">{{ $edu['institution'] }}</p>
                            @endif
                        </div>
                        <p class="entry-dates">
                            @if(!empty($edu['from']))
                                {{ date('M Y', strtotime($edu['from'] . '-01')) }}
                            @endif
                            @if(!empty($edu['from']) && !empty($edu['to']))
                                –
                            @endif
                            @if(!empty($edu['to']))
                                {{ date('M Y', strtotime($edu['to'] . '-01')) }}
                            @endif
                        </p>
                    </div>
                    @if(!empty($edu['description']))
                        <p>{{ $edu['description'] }}</p>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    @if(!empty($projects))
        <section class="section">
            <h2>{{ __('Projects') }}</h2>
            @foreach($projects as $project)
                <article class="entry">
                    <div class="entry-header">
                        <div>
                            <h3>{{ $project['title'] ?? '' }}</h3>
                            @if(!empty($project['url']))
                                <p class="entry-subtitle"><a href="{{ $project['url'] }}">{{ $project['url'] }}</a></p>
                            @endif
                        </div>
                        <p class="entry-dates">
                            @if(!empty($project['from']))
                                {{ date('M Y', strtotime($project['from'] . '-01')) }}
                            @endif
                            @if(!empty($project['from']) && (!empty($project['to']) || ($project['current'] ?? false)))
                                –
                            @endif
                            @if($project['current'] ?? false)
                                {{ __('Present') }}
                            @elseif(!empty($project['to']))
                                {{ date('M Y', strtotime($project['to'] . '-01')) }}
                            @endif
                        </p>
                    </div>
                    @if(!empty($project['description']))
                        <p>{{ $project['description'] }}</p>
                    @endif
                    @if(!empty($project['technologies']))
                        <p>{{ __('Technologies') }}: {{ $project['technologies'] }}</p>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    @if(!empty($languages))
        <section class="section">
            <h2>{{ __('Languages') }}</h2>
            @foreach($languages as $lang)
                @php
                    $level = $lang['proficiencyLevel'] ?? 1;
                @endphp
                <p class="language-row">
                    {{ $lang['name'] ?? '' }} – {{ $levelNames[$level] ?? __('Beginner') }}
                </p>
            @endforeach
        </section>
    @endif

    @if(!empty($interests))
        <section class="section">
            <h2>{{ __('Interests') }}</h2>
            <p class="interests-list">
                {{ collect($interests)->pluck('name')->filter()->implode(', ') }}
            </p>
        </section>
    @endif
</x-template-layout>
