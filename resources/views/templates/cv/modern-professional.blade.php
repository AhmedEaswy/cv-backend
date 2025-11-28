<!DOCTYPE html>
<html lang="{{ $cv['language'] ?? 'en' }}" dir="{{ in_array($cv['language'] ?? 'en', ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($cv['user_data']['firstName'] ?? '') . ' ' . ($cv['user_data']['lastName'] ?? '') }} - CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            background: white;
        }

        /* Page break rules for sections */
        .section {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Major sections should start on a new page if there's not enough space */
        .major-section {
            page-break-before: auto;
            break-before: auto;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Force page break before major sections (uncomment if needed) */
        /* .major-section:not(:first-child) {
            page-break-before: always;
            break-before: page;
        } */

        /* Prevent individual items from being split */
        .experience-item,
        .education-item,
        .project-item {
            page-break-inside: avoid;
            break-inside: avoid;
            margin-bottom: 1rem;
        }

        /* Ensure headers stay with their content */
        h2 {
            page-break-after: avoid;
            break-after: avoid;
        }

        /* Prevent header section from splitting */
        .header-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>
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
            $contactInfo = [];
            if (!empty($userData['email'])) $contactInfo[] = $userData['email'];
            if (!empty($userData['phone'])) $contactInfo[] = $userData['phone'];
            if (!empty($userData['address'])) $contactInfo[] = $userData['address'];
            if (!empty($userData['portfolioUrl'])) $contactInfo[] = $userData['portfolioUrl'];
        @endphp

        <!-- Header Section -->
        <div class="header-section mb-6 border-b-4 border-blue-600 pb-4">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $fullName }}</h1>
            @if($jobTitle)
                <p class="text-xl text-gray-600 mb-3">{{ $jobTitle }}</p>
            @endif
            @if(!empty($contactInfo))
                <div class="flex flex-wrap gap-4 text-sm text-gray-700">
                    @foreach($contactInfo as $info)
                        <span>{{ $info }}</span>
                        @if(!$loop->last)
                            <span class="text-gray-400">•</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Summary Section -->
        @if($summary)
            <div class="section mb-6">
                <h2 class="text-2xl font-bold text-blue-600 mb-3 border-b-2 border-blue-200 pb-1">Summary</h2>
                <p class="text-gray-700 leading-relaxed">{{ $summary }}</p>
            </div>
        @endif

        <!-- Skills Section -->
        @if(!empty($skills))
            <div class="section mb-6">
                <h2 class="text-2xl font-bold text-blue-600 mb-3 border-b-2 border-blue-200 pb-1">Skills</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($skills as $skill)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            {{ $skill['name'] ?? '' }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Work Experience Section -->
        @if(!empty($experiences))
            <div class="major-section mb-6">
                <h2 class="text-2xl font-bold text-blue-600 mb-3 border-b-2 border-blue-200 pb-1">Work Experience</h2>
                <div class="space-y-4">
                    @foreach($experiences as $exp)
                        <div class="experience-item border-l-4 border-blue-600 pl-4">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $exp['position'] ?? '' }}</h3>
                                    @if(!empty($exp['company']))
                                        <p class="text-gray-700 font-medium">{{ $exp['company'] }}</p>
                                    @endif
                                    @if(!empty($exp['location']))
                                        <p class="text-sm text-gray-600">{{ $exp['location'] }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm text-gray-600">
                                    @if(!empty($exp['from']))
                                        <span>{{ date('M Y', strtotime($exp['from'] . '-01')) }}</span>
                                    @endif
                                    @if(!empty($exp['from']) || !empty($exp['to']) || ($exp['current'] ?? false))
                                        <span> - </span>
                                    @endif
                                    @if($exp['current'] ?? false)
                                        <span class="font-medium">Present</span>
                                    @elseif(!empty($exp['to']))
                                        <span>{{ date('M Y', strtotime($exp['to'] . '-01')) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($exp['description']))
                                <p class="text-gray-700 mt-2 leading-relaxed">{{ $exp['description'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Education Section -->
        @if(!empty($educations))
            <div class="major-section mb-6">
                <h2 class="text-2xl font-bold text-blue-600 mb-3 border-b-2 border-blue-200 pb-1">Education</h2>
                <div class="space-y-4">
                    @foreach($educations as $edu)
                        <div class="education-item border-l-4 border-green-600 pl-4">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $edu['degree'] ?? '' }}</h3>
                                    @if(!empty($edu['fieldOfStudy']))
                                        <p class="text-gray-700 font-medium">{{ $edu['fieldOfStudy'] }}</p>
                                    @endif
                                    @if(!empty($edu['institution']))
                                        <p class="text-gray-700">{{ $edu['institution'] }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm text-gray-600">
                                    @if(!empty($edu['from']))
                                        <span>{{ date('M Y', strtotime($edu['from'] . '-01')) }}</span>
                                    @endif
                                    @if((!empty($edu['from']) || !empty($edu['to'])) && (!empty($edu['from']) && !empty($edu['to'])))
                                        <span> - </span>
                                    @endif
                                    @if(!empty($edu['to']))
                                        <span>{{ date('M Y', strtotime($edu['to'] . '-01')) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($edu['description']))
                                <p class="text-gray-700 mt-2 leading-relaxed">{{ $edu['description'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Projects Section -->
        @if(!empty($projects))
            <div class="major-section mb-6">
                <h2 class="text-2xl font-bold text-blue-600 mb-3 border-b-2 border-blue-200 pb-1">Projects</h2>
                <div class="space-y-4">
                    @foreach($projects as $project)
                        <div class="project-item border-l-4 border-purple-600 pl-4">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $project['title'] ?? '' }}</h3>
                                    @if(!empty($project['url']))
                                        <a href="{{ $project['url'] }}" class="text-blue-600 hover:underline text-sm">{{ $project['url'] }}</a>
                                    @endif
                                </div>
                                <div class="text-right text-sm text-gray-600">
                                    @if(!empty($project['from']))
                                        <span>{{ date('M Y', strtotime($project['from'] . '-01')) }}</span>
                                    @endif
                                    @if((!empty($project['from']) || !empty($project['to'])) && (!empty($project['from']) && !empty($project['to'])))
                                        <span> - </span>
                                    @endif
                                    @if($project['current'] ?? false)
                                        <span class="font-medium">Present</span>
                                    @elseif(!empty($project['to']))
                                        <span>{{ date('M Y', strtotime($project['to'] . '-01')) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($project['description']))
                                <p class="text-gray-700 mt-2 leading-relaxed">{{ $project['description'] }}</p>
                            @endif
                            @if(!empty($project['technologies']))
                                <p class="text-sm text-gray-600 mt-2 italic">Technologies: {{ $project['technologies'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Languages Section -->
        @if(!empty($languages))
            <div class="section mb-6">
                <h2 class="text-2xl font-bold text-blue-600 mb-3 border-b-2 border-blue-200 pb-1">Languages</h2>
                <div class="grid grid-cols-2 gap-4">
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
            </div>
        @endif

        <!-- Interests Section -->
        @if(!empty($interests))
            <div class="section mb-6">
                <h2 class="text-2xl font-bold text-blue-600 mb-3 border-b-2 border-blue-200 pb-1">Interests</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($interests as $interest)
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                            {{ $interest['name'] ?? '' }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</body>
</html>

