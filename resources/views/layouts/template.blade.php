<!DOCTYPE html>
<html lang="{{ $cv['language'] ?? 'en' }}" dir="{{ in_array($cv['language'] ?? 'en', ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($cv['user_data']['firstName'] ?? '') . ' ' . ($cv['user_data']['lastName'] ?? '') }} - CV</title>

    {{-- Template-specific head content (fonts, icons, etc.) --}}
    @stack('head')

    {{-- Template-specific Tailwind config (must be before Tailwind CSS) --}}
    @stack('tailwind-config')

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

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
            page-break-before: auto;
            break-before: auto;
            page-break-inside: avoid;
            break-inside: avoid;
        }

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

    {{-- Template-specific styles --}}
    @stack('styles')
</head>
<body @yield('body-attributes', '')>
    <div class="page">
        @php
            // Common data extraction for all templates
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
            $address = $userData['address'] ?? '';

            // Contact info array for templates that need it
            $contactInfo = [];
            if (!empty($email)) $contactInfo[] = $email;
            if (!empty($phone)) $contactInfo[] = $phone;
            if (!empty($address)) $contactInfo[] = $address;
            if (!empty($portfolioUrl)) $contactInfo[] = $portfolioUrl;
        @endphp

        {{-- Template-specific content --}}
        @yield('content')
    </div>
</body>
</html>

