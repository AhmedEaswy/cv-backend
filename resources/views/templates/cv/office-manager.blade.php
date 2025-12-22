<!DOCTYPE html>
<html lang="{{ $cv['language'] ?? 'en' }}" dir="{{ in_array($cv['language'] ?? 'en', ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($cv['user_data']['firstName'] ?? '') . ' ' . ($cv['user_data']['lastName'] ?? '') }} - CV</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#2563eb",
                        "background-light": "#ffffff",
                        "background-dark": "#111827",
                        "paper-light": "#f9fafb",
                        "paper-dark": "#1f2937",
                    },
                    fontFamily: {
                        display: ["'Playfair Display'", "serif"],
                        body: ["'Merriweather'", "serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                    },
                },
            },
        };
    </script>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        html {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: white !important;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            background: white;
        }
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .page {
                padding: 15mm;
            }
        }

        /* Page break rules for sections */
        .section {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .major-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .experience-item,
        .education-item {
            page-break-inside: avoid;
            break-inside: avoid;
            margin-bottom: 1rem;
        }

        h2 {
            page-break-after: avoid;
            break-after: avoid;
        }

        .header-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body class="bg-background-light text-gray-900 font-body antialiased">
    <div class="page">
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
            $email = $userData['email'] ?? '';
            $phone = $userData['phone'] ?? '';
            $portfolioUrl = $userData['portfolioUrl'] ?? '';
        @endphp

        <!-- Header Section -->
        <header class="header-section mb-10 text-left">
            <h1 class="text-5xl md:text-6xl font-display font-black tracking-tight text-gray-900 mb-3 leading-tight">
                @if(!empty($userData['firstName']))
                    {{ $userData['firstName'] }}
                    @if(!empty($userData['lastName']))<br/>{{ $userData['lastName'] }}@endif
                @else
                    {{ $fullName }}
                @endif
            </h1>
            @if($jobTitle)
                <p class="text-sm font-bold tracking-widest uppercase text-gray-500 mb-8">
                    {{ $jobTitle }}
                </p>
            @endif
            @if($email || $phone || $portfolioUrl)
                <div class="border-t border-b border-gray-300 py-4 flex flex-col md:flex-row md:justify-between gap-4 text-sm">
                    @if($email)
                        <a class="flex items-center gap-2 text-gray-700 hover:text-primary transition-colors" href="mailto:{{ $email }}">
                            <span class="material-icons text-lg text-gray-400">email</span>
                            <span>{{ $email }}</span>
                        </a>
                    @endif
                    @if($phone)
                        <a class="flex items-center gap-2 text-gray-700 hover:text-primary transition-colors" href="tel:{{ $phone }}">
                            <span class="material-icons text-lg text-gray-400">phone</span>
                            <span>{{ $phone }}</span>
                        </a>
                    @endif
                    @if($portfolioUrl)
                        <a class="flex items-center gap-2 text-gray-700 hover:text-primary transition-colors" href="{{ $portfolioUrl }}" target="_blank">
                            <span class="material-icons text-lg text-gray-400">link</span>
                            <span>{{ $userData['address'] ?? 'Portfolio' }}</span>
                        </a>
                    @elseif(!empty($userData['address']))
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="material-icons text-lg text-gray-400">location_on</span>
                            <span>{{ $userData['address'] }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </header>

        <!-- Summary Section -->
        @if($summary)
            <section class="section mb-12">
                <h2 class="text-lg font-bold uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-6 font-display text-gray-900">
                    Summary
                </h2>
                <p class="text-gray-600 leading-relaxed text-sm">{{ $summary }}</p>
            </section>
        @endif

        <!-- Work Experience Section -->
        @if(!empty($experiences))
            <section class="major-section mb-12">
                <h2 class="text-lg font-bold uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-6 font-display text-gray-900">
                    Experience
                </h2>
                @foreach($experiences as $index => $exp)
                    <div class="experience-item mb-8 relative pl-4 border-l-2 border-gray-200 {{ !$loop->last ? '' : 'border-transparent' }}">
                        @if(!$loop->last)
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-2 border-gray-300"></div>
                        @endif
                        <div class="mb-2">
                            <span class="block text-xs font-semibold uppercase text-primary mb-1">
                                @if(!empty($exp['from']))
                                    {{ date('M Y', strtotime($exp['from'] . '-01')) }}
                                @endif
                                @if(!empty($exp['from']) || !empty($exp['to']) || ($exp['current'] ?? false))
                                    <span> - </span>
                                @endif
                                @if($exp['current'] ?? false)
                                    <span>Current</span>
                                @elseif(!empty($exp['to']))
                                    {{ date('M Y', strtotime($exp['to'] . '-01')) }}
                                @endif
                            </span>
                            <h3 class="text-xl font-bold font-display text-gray-900">
                                {{ $exp['position'] ?? '' }}{{ !empty($exp['company']) ? ', ' : '' }}<span class="font-normal italic text-gray-600 font-body">{{ $exp['company'] ?? '' }}</span>
                            </h3>
                            @if(!empty($exp['location']))
                                <p class="text-sm text-gray-500 mt-1">{{ $exp['location'] }}</p>
                            @endif
                        </div>
                        @if(!empty($exp['description']))
                            <p class="text-gray-600 leading-relaxed text-sm">{{ $exp['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        <!-- Education Section -->
        @if(!empty($educations))
            <section class="major-section mb-12">
                <h2 class="text-lg font-bold uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-6 font-display text-gray-900">
                    Education
                </h2>
                @foreach($educations as $edu)
                    <div class="education-item flex flex-col md:flex-row md:items-baseline gap-2 md:gap-4 mb-4">
                        <span class="text-sm font-semibold text-primary md:w-32 flex-shrink-0">
                            @if(!empty($edu['from']))
                                {{ date('M Y', strtotime($edu['from'] . '-01')) }}
                            @endif
                            @if((!empty($edu['from']) || !empty($edu['to'])) && (!empty($edu['from']) && !empty($edu['to'])))
                                <span> - </span>
                            @endif
                            @if(!empty($edu['to']))
                                {{ date('M Y', strtotime($edu['to'] . '-01')) }}
                            @endif
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                {{ $edu['degree'] ?? '' }}{{ !empty($edu['fieldOfStudy']) ? ', ' : '' }}<span class="font-normal text-gray-600">{{ $edu['fieldOfStudy'] ?? '' }}</span>
                            </h3>
                            @if(!empty($edu['institution']))
                                <p class="text-gray-500 italic">{{ $edu['institution'] }}</p>
                            @endif
                            @if(!empty($edu['description']))
                                <p class="text-gray-600 text-sm mt-1">{{ $edu['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        <!-- Skills Section -->
        @if(!empty($skills))
            <section class="section mb-12">
                <h2 class="text-lg font-bold uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-6 font-display text-gray-900">
                    Skills
                </h2>
                @php
                    $skillsPerColumn = ceil(count($skills) / 3);
                    $skillChunks = array_chunk($skills, $skillsPerColumn);
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8">
                    @foreach($skillChunks as $chunk)
                        <ul class="space-y-2">
                            @foreach($chunk as $skill)
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    <span class="text-gray-700">{{ $skill['name'] ?? '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Projects Section -->
        @if(!empty($projects))
            <section class="major-section mb-12">
                <h2 class="text-lg font-bold uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-6 font-display text-gray-900">
                    Projects
                </h2>
                @foreach($projects as $project)
                    <div class="experience-item mb-8 relative pl-4 border-l-2 border-gray-200">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-2 border-gray-300"></div>
                        <div class="mb-2">
                            <h3 class="text-xl font-bold font-display text-gray-900">
                                {{ $project['title'] ?? '' }}
                            </h3>
                            @if(!empty($project['url']))
                                <a href="{{ $project['url'] }}" class="text-primary hover:underline text-sm" target="_blank">{{ $project['url'] }}</a>
                            @endif
                            @if(!empty($project['from']) || !empty($project['to']) || ($project['current'] ?? false))
                                <span class="block text-xs font-semibold uppercase text-primary mt-1">
                                    @if(!empty($project['from']))
                                        {{ date('M Y', strtotime($project['from'] . '-01')) }}
                                    @endif
                                    @if(!empty($project['from']) || !empty($project['to']) || ($project['current'] ?? false))
                                        <span> - </span>
                                    @endif
                                    @if($project['current'] ?? false)
                                        <span>Current</span>
                                    @elseif(!empty($project['to']))
                                        {{ date('M Y', strtotime($project['to'] . '-01')) }}
                                    @endif
                                </span>
                            @endif
                        </div>
                        @if(!empty($project['description']))
                            <p class="text-gray-600 leading-relaxed text-sm">{{ $project['description'] }}</p>
                        @endif
                        @if(!empty($project['technologies']))
                            <p class="text-sm text-gray-500 mt-2 italic">Technologies: {{ $project['technologies'] }}</p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        <!-- Languages Section -->
        @if(!empty($languages))
            <section class="section mb-12">
                <h2 class="text-lg font-bold uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-6 font-display text-gray-900">
                    Languages
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                    @foreach($languages as $lang)
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-900">{{ $lang['name'] ?? '' }}</span>
                            @php
                                $level = $lang['proficiencyLevel'] ?? 1;
                                $levelNames = [1 => 'Beginner', 2 => 'Intermediate', 3 => 'Advanced', 4 => 'Fluent', 5 => 'Native'];
                            @endphp
                            <span class="text-sm text-gray-600">{{ $levelNames[$level] ?? 'Beginner' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Interests Section -->
        @if(!empty($interests))
            <section class="section mb-12">
                <h2 class="text-lg font-bold uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-6 font-display text-gray-900">
                    Interests
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8">
                    @foreach($interests as $interest)
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            <span class="text-gray-700">{{ $interest['name'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</body>
</html>

