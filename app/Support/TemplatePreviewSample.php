<?php

namespace App\Support;

/**
 * Fixed sample content used when capturing template preview images.
 * The same person appears on every template so cards are comparable.
 */
class TemplatePreviewSample
{
    public static function cv(): array
    {
        return [
            'language' => 'en',
            'user_data' => [
                'firstName' => 'Elena',
                'lastName' => 'Voss',
                'jobTitle' => 'Product Designer',
                'email' => 'elena.voss@example.com',
                'phone' => '+1 415 555 0148',
                'address' => 'San Francisco, CA',
                'portfolioUrl' => 'https://elenavoss.example',
                'photo' => self::portrait(),
                'summary' => 'Product designer who turns messy workflows into calm interfaces. Ten years shipping design systems, hiring tools, and customer portals for teams that care about clarity.',
                'skills' => self::skills(),
                'experiences' => [
                    [
                        'position' => 'Senior Product Designer',
                        'company' => 'Northwind',
                        'location' => 'San Francisco',
                        'from' => 'Mar 2021',
                        'current' => true,
                        'description' => 'Led the hiring-suite redesign used by 400 recruiters. Built a shared component library and cut design-to-ship time from three weeks to six days.',
                    ],
                    [
                        'position' => 'Product Designer',
                        'company' => 'Lumen Health',
                        'location' => 'Remote',
                        'from' => 'Jun 2018',
                        'to' => 'Feb 2021',
                        'description' => 'Designed the patient portal and clinician handoff flow. Partnered with research to replace a 14-step intake with a four-step one.',
                    ],
                ],
                'educations' => [
                    [
                        'degree' => 'BFA',
                        'fieldOfStudy' => 'Graphic Design',
                        'institution' => 'Rhode Island School of Design',
                        'from' => '2014',
                        'to' => '2018',
                    ],
                ],
                'projects' => [
                    [
                        'title' => 'Atlas Design System',
                        'url' => 'https://atlas.example',
                        'description' => 'Tokens, components, and documentation adopted across twelve product teams.',
                    ],
                ],
                'languages' => [
                    ['name' => 'English', 'proficiencyLevel' => 5],
                    ['name' => 'German', 'proficiencyLevel' => 3],
                ],
                'interests' => [
                    ['name' => 'Letterpress'],
                    ['name' => 'Trail running'],
                    ['name' => 'City mapping'],
                ],
            ],
        ];
    }

    public static function coverLetter(): array
    {
        return [
            'language' => 'en',
            'user_data' => [
                'firstName' => 'Elena',
                'lastName' => 'Voss',
                'jobTitle' => 'Product Designer',
                'email' => 'elena.voss@example.com',
                'phone' => '+1 415 555 0148',
                'address' => 'San Francisco, CA',
                'recipientName' => 'Jordan Hale',
                'recipientTitle' => 'Head of Design',
                'recipientCompany' => 'Harbor',
                'companyName' => 'Harbor',
                'subject' => 'Application for Senior Product Designer',
                'body' => implode("\n\n", [
                    'I am writing to apply for the Senior Product Designer role at Harbor. Your work on calm, precise tools for operators is the kind of product I want to spend the next few years on.',
                    'At Northwind I led the hiring-suite redesign and the design system behind it. Recruiters finished reviews faster, and twelve teams shipped from the same components. Before that I simplified a clinical intake from fourteen steps to four.',
                    'I would welcome a conversation about how that experience could help Harbor’s next release. Thank you for your time.',
                ]),
                'closing' => 'Sincerely',
            ],
        ];
    }

    public static function publicProfile(): array
    {
        return [
            'language' => 'en',
            'headline' => 'Product designer for calm, precise software.',
            'about' => 'I design hiring tools, patient portals, and design systems. I like clear type, short flows, and teams that ship.',
            'sections_order' => [
                'about', 'services', 'experiences', 'projects', 'skills',
                'educations', 'certifications', 'achievements', 'languages',
                'testimonials', 'availability',
            ],
            'user_data' => [
                'firstName' => 'Elena',
                'lastName' => 'Voss',
                'jobTitle' => 'Product Designer',
                'email' => 'elena.voss@example.com',
                'phone' => '+1 415 555 0148',
                'city' => 'San Francisco',
                'country' => 'United States',
                'website' => 'https://elenavoss.example',
                'photo' => self::portrait(),
                'socialLinks' => [
                    ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/example'],
                    ['label' => 'Dribbble', 'url' => 'https://dribbble.com/example'],
                ],
                'experiences' => [
                    [
                        'position' => 'Senior Product Designer',
                        'company' => 'Northwind',
                        'from' => '2021-03',
                        'current' => true,
                        'description' => 'Hiring suite and design system used by 400 recruiters.',
                    ],
                    [
                        'position' => 'Product Designer',
                        'company' => 'Lumen Health',
                        'from' => '2018-06',
                        'to' => '2021-02',
                        'description' => 'Patient portal and clinician handoff.',
                    ],
                ],
                'educations' => [
                    [
                        'degree' => 'BFA Graphic Design',
                        'institution' => 'Rhode Island School of Design',
                        'from' => '2014-09',
                        'to' => '2018-05',
                    ],
                ],
                'projects' => [
                    [
                        'title' => 'Atlas Design System',
                        'description' => 'Tokens and components adopted by twelve product teams.',
                        'url' => 'https://atlas.example',
                        'technologies' => ['Figma', 'React'],
                        'image' => self::swatch('#0f766e', '#134e4a', 'Atlas'),
                    ],
                    [
                        'title' => 'Harbor Hiring',
                        'description' => 'A quieter review flow for recruiters.',
                        'url' => 'https://harbor.example',
                        'technologies' => ['Research', 'UI'],
                        'image' => self::swatch('#1e3a5f', '#0f172a', 'Harbor'),
                    ],
                    [
                        'title' => 'Lumen Intake',
                        'description' => 'Fourteen steps reduced to four.',
                        'technologies' => ['Health', 'UX'],
                        'image' => self::swatch('#9a3412', '#7c2d12', 'Lumen'),
                    ],
                ],
                'skills' => self::skills(),
                'languages' => [
                    ['name' => 'English', 'proficiencyLevel' => 5],
                    ['name' => 'German', 'proficiencyLevel' => 3],
                ],
                'services' => [
                    ['title' => 'Product design', 'description' => 'Flows, interface, and the system behind them.'],
                    ['title' => 'Design systems', 'description' => 'Tokens, components, and documentation teams actually use.'],
                ],
                'testimonials' => [
                    [
                        'quote' => 'Elena made a complicated hiring tool feel obvious.',
                        'name' => 'Jordan Hale',
                        'role' => 'Head of Design, Harbor',
                    ],
                ],
                'certifications' => [
                    ['name' => 'Nielsen Norman UX Certification', 'issuer' => 'NN/g', 'year' => '2020'],
                ],
                'achievements' => [
                    ['title' => 'Design system adopted company-wide', 'year' => '2023'],
                ],
                'availability' => [
                    'status' => 'Open to select projects',
                    'note' => 'Booking for Q3.',
                ],
                'cta' => [
                    'label' => 'Start a project',
                    'url' => 'mailto:elena.voss@example.com',
                ],
            ],
        ];
    }

    private static function skills(): array
    {
        return [
            ['name' => 'Product design'],
            ['name' => 'Design systems'],
            ['name' => 'Prototyping'],
            ['name' => 'User research'],
            ['name' => 'Figma'],
            ['name' => 'Typography'],
        ];
    }

    private static function portrait(): string
    {
        return self::svgDataUri(<<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <rect width="400" height="400" fill="#134e4a"/>
  <circle cx="200" cy="156" r="74" fill="#fde68a"/>
  <path d="M72 420c8-120 62-168 128-168s120 48 128 168" fill="#f8fafc"/>
</svg>
SVG);
    }

    private static function swatch(string $from, string $to, string $label): string
    {
        $safe = htmlspecialchars($label, ENT_QUOTES);

        return self::svgDataUri(<<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$from}"/>
      <stop offset="1" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="800" height="600" fill="url(#g)"/>
  <text x="48" y="540" fill="#ffffff" font-family="Georgia, serif" font-size="42">{$safe}</text>
</svg>
SVG);
    }

    private static function svgDataUri(string $svg): string
    {
        return 'data:image/svg+xml;charset=UTF-8,'.rawurlencode($svg);
    }
}
