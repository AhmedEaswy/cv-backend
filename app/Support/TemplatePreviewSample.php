<?php

namespace App\Support;

/**
 * Fixed sample content used when capturing template preview images.
 * The same person appears on every template so cards are comparable.
 */
class TemplatePreviewSample
{
    public static function cv(string $locale = 'en'): array
    {
        return $locale === 'ar' ? self::cvAr() : self::cvEn();
    }

    public static function coverLetter(string $locale = 'en'): array
    {
        return $locale === 'ar' ? self::coverLetterAr() : self::coverLetterEn();
    }

    public static function publicProfile(string $locale = 'en'): array
    {
        return $locale === 'ar' ? self::publicProfileAr() : self::publicProfileEn();
    }

    private static function cvEn(): array
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
                'skills' => self::skillsEn(),
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

    private static function cvAr(): array
    {
        return [
            'language' => 'ar',
            'user_data' => [
                'firstName' => 'أحمد',
                'lastName' => 'علي',
                'jobTitle' => 'مصمم منتجات',
                'email' => 'ahmed.ali@example.com',
                'phone' => '+20 100 123 4567',
                'address' => 'القاهرة، مصر',
                'portfolioUrl' => 'https://ahmedali.example',
                'photo' => self::portrait(),
                'summary' => 'مصمم منتجات يحوّل سير العمل المعقّد إلى واجهات هادئة وواضحة. عشر سنوات في بناء أنظمة التصميم وأدوات التوظيف وبوابات العملاء للفرق التي تهتم بالوضوح.',
                'skills' => self::skillsAr(),
                'experiences' => [
                    [
                        'position' => 'مصمم منتجات أول',
                        'company' => 'نورث ويند',
                        'location' => 'القاهرة',
                        'from' => 'مارس 2021',
                        'current' => true,
                        'description' => 'قاد إعادة تصميم منصة التوظيف المستخدمة من ٤٠٠ مسؤول توظيف. بنى مكتبة مكوّنات مشتركة وقلّص زمن التصميم إلى الإطلاق من ثلاثة أسابيع إلى ستة أيام.',
                    ],
                    [
                        'position' => 'مصمم منتجات',
                        'company' => 'لومن هيلث',
                        'location' => 'عن بُعد',
                        'from' => 'يونيو 2018',
                        'to' => 'فبراير 2021',
                        'description' => 'صمّم بوابة المرضى وتدفق تسليم الأطباء. بالشراكة مع البحث استُبدلت خطوات الاستقبال الأربع عشرة بأربع خطوات فقط.',
                    ],
                ],
                'educations' => [
                    [
                        'degree' => 'بكالوريوس فنون جميلة',
                        'fieldOfStudy' => 'تصميم جرافيكي',
                        'institution' => 'كلية الفنون الجميلة',
                        'from' => '2014',
                        'to' => '2018',
                    ],
                ],
                'projects' => [
                    [
                        'title' => 'نظام تصميم أطلس',
                        'url' => 'https://atlas.example',
                        'description' => 'رموز ومكوّنات وتوثيق اعتمدتها اثنا عشر فريق منتج.',
                    ],
                ],
                'languages' => [
                    ['name' => 'العربية', 'proficiencyLevel' => 5],
                    ['name' => 'الإنجليزية', 'proficiencyLevel' => 4],
                ],
                'interests' => [
                    ['name' => 'الطباعة الفنية'],
                    ['name' => 'الجري في الطبيعة'],
                    ['name' => 'رسم الخرائط'],
                ],
            ],
        ];
    }

    private static function coverLetterEn(): array
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

    private static function coverLetterAr(): array
    {
        return [
            'language' => 'ar',
            'user_data' => [
                'firstName' => 'أحمد',
                'lastName' => 'علي',
                'jobTitle' => 'مصمم منتجات',
                'email' => 'ahmed.ali@example.com',
                'phone' => '+20 100 123 4567',
                'address' => 'القاهرة، مصر',
                'recipientName' => 'سارة حسن',
                'recipientTitle' => 'رئيسة التصميم',
                'recipientCompany' => 'هاربر',
                'companyName' => 'هاربر',
                'subject' => 'طلب وظيفة مصمم منتجات أول',
                'body' => implode("\n\n", [
                    'أتقدم بطلبي لشغل وظيفة مصمم منتجات أول في هاربر. أعمالكم على أدوات هادئة ودقيقة للمشغّلين هي نوع المنتجات التي أرغب في العمل عليها خلال السنوات القادمة.',
                    'في نورث ويند قدت إعادة تصميم منصة التوظيف ونظام التصميم خلفها. أصبح مسؤولو التوظيف ينهون المراجعات أسرع، واعتمد اثنا عشر فريقاً المكوّنات نفسها. قبل ذلك بسّطت استقبالاً سريرياً من أربع عشرة خطوة إلى أربع.',
                    'يسعدني مناقشة كيف يمكن لهذه الخبرة أن تساهم في الإصدار القادم لهاربر. شكراً لوقتكم.',
                ]),
                'closing' => 'مع أطيب التحيات',
            ],
        ];
    }

    private static function publicProfileEn(): array
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
                'skills' => self::skillsEn(),
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

    private static function publicProfileAr(): array
    {
        return [
            'language' => 'ar',
            'headline' => 'مصمم منتجات لبرمجيات هادئة ودقيقة.',
            'about' => 'أصمّم أدوات التوظيف وبوابات المرضى وأنظمة التصميم. أحبّ الخط الواضح والتدفقات القصيرة والفرق التي تُنجز.',
            'sections_order' => [
                'about', 'services', 'experiences', 'projects', 'skills',
                'educations', 'certifications', 'achievements', 'languages',
                'testimonials', 'availability',
            ],
            'user_data' => [
                'firstName' => 'أحمد',
                'lastName' => 'علي',
                'jobTitle' => 'مصمم منتجات',
                'email' => 'ahmed.ali@example.com',
                'phone' => '+20 100 123 4567',
                'city' => 'القاهرة',
                'country' => 'مصر',
                'website' => 'https://ahmedali.example',
                'photo' => self::portrait(),
                'socialLinks' => [
                    ['label' => 'لينكدإن', 'url' => 'https://www.linkedin.com/in/example'],
                    ['label' => 'دريبل', 'url' => 'https://dribbble.com/example'],
                ],
                'experiences' => [
                    [
                        'position' => 'مصمم منتجات أول',
                        'company' => 'نورث ويند',
                        'from' => '2021-03',
                        'current' => true,
                        'description' => 'منصة التوظيف ونظام التصميم المستخدم من ٤٠٠ مسؤول توظيف.',
                    ],
                    [
                        'position' => 'مصمم منتجات',
                        'company' => 'لومن هيلث',
                        'from' => '2018-06',
                        'to' => '2021-02',
                        'description' => 'بوابة المرضى وتدفق تسليم الأطباء.',
                    ],
                ],
                'educations' => [
                    [
                        'degree' => 'بكالوريوس تصميم جرافيكي',
                        'institution' => 'كلية الفنون الجميلة',
                        'from' => '2014-09',
                        'to' => '2018-05',
                    ],
                ],
                'projects' => [
                    [
                        'title' => 'نظام تصميم أطلس',
                        'description' => 'رموز ومكوّنات اعتمدتها اثنا عشر فريق منتج.',
                        'url' => 'https://atlas.example',
                        'technologies' => ['Figma', 'React'],
                        'image' => self::swatch('#0f766e', '#134e4a', 'أطلس'),
                    ],
                    [
                        'title' => 'توظيف هاربر',
                        'description' => 'تدفق مراجعة أكثر هدوءاً لمسؤولي التوظيف.',
                        'url' => 'https://harbor.example',
                        'technologies' => ['بحث', 'واجهة'],
                        'image' => self::swatch('#1e3a5f', '#0f172a', 'هاربر'),
                    ],
                    [
                        'title' => 'استقبال لومن',
                        'description' => 'أربع عشرة خطوة اختُصرت إلى أربع.',
                        'technologies' => ['صحة', 'تجربة مستخدم'],
                        'image' => self::swatch('#9a3412', '#7c2d12', 'لومن'),
                    ],
                ],
                'skills' => self::skillsAr(),
                'languages' => [
                    ['name' => 'العربية', 'proficiencyLevel' => 5],
                    ['name' => 'الإنجليزية', 'proficiencyLevel' => 4],
                ],
                'services' => [
                    ['title' => 'تصميم المنتجات', 'description' => 'التدفقات والواجهة والنظام خلفها.'],
                    ['title' => 'أنظمة التصميم', 'description' => 'رموز ومكوّنات وتوثيق تستخدمه الفرق فعلاً.'],
                ],
                'testimonials' => [
                    [
                        'quote' => 'جعل أحمد أداة توظيف معقّدة تبدو بديهية.',
                        'name' => 'سارة حسن',
                        'role' => 'رئيسة التصميم، هاربر',
                    ],
                ],
                'certifications' => [
                    ['name' => 'شهادة نيلسن نورمان لتجربة المستخدم', 'issuer' => 'NN/g', 'year' => '2020'],
                ],
                'achievements' => [
                    ['title' => 'اعتماد نظام التصميم على مستوى الشركة', 'year' => '2023'],
                ],
                'availability' => [
                    'status' => 'متاح لمشاريع مختارة',
                    'note' => 'الحجز للربع الثالث.',
                ],
                'cta' => [
                    'label' => 'ابدأ مشروعاً',
                    'url' => 'mailto:ahmed.ali@example.com',
                ],
            ],
        ];
    }

    private static function skillsEn(): array
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

    private static function skillsAr(): array
    {
        return [
            ['name' => 'تصميم المنتجات'],
            ['name' => 'أنظمة التصميم'],
            ['name' => 'النماذج الأولية'],
            ['name' => 'بحث المستخدمين'],
            ['name' => 'فيجما'],
            ['name' => 'الطباعة'],
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
