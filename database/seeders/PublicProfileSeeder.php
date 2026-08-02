<?php

namespace Database\Seeders;

use App\Models\PublicProfile;
use App\Models\PublicProfileTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublicProfileSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@app.com')->first() ?? User::first();

        if (! $user) {
            $this->command->warn('No users found. Skipping public profile seeder.');

            return;
        }

        $defaultTemplate = PublicProfileTemplate::where('is_default', true)->first()
            ?? PublicProfileTemplate::where('is_active', true)->first();

        if (! $defaultTemplate) {
            $this->command->warn('No public profile templates found. Run PublicProfileTemplateSeeder first.');

            return;
        }

        PublicProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'public_profile_template_id' => $defaultTemplate->id,
                'slug' => 'jane-doe',
                'is_public' => true,
                'language' => 'en',
                'headline' => 'Product designer & full-stack builder',
                'about' => 'I help teams ship clear, useful products — from early sketches to production code. Currently open to select freelance collaborations and advisory work.',
                'info' => [
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'jobTitle' => 'Senior Product Designer',
                    'email' => 'jane.doe@example.com',
                    'phone' => '+1-555-010-2000',
                    'city' => 'San Francisco',
                    'country' => 'USA',
                    'website' => 'https://example.com',
                    'photo' => null,
                ],
                'social_links' => [
                    'linkedin' => 'https://linkedin.com/in/example',
                    'github' => 'https://github.com/example',
                    'twitter' => 'https://twitter.com/example',
                ],
                'experiences' => [
                    [
                        'position' => 'Senior Product Designer',
                        'company' => 'Northwind Labs',
                        'from' => '2022-03',
                        'to' => null,
                        'current' => true,
                        'description' => 'Lead design for the core SaaS platform used by 40k+ teams. Built design systems and partnered with engineering on Laravel + React deliveries.',
                    ],
                    [
                        'position' => 'Product Designer',
                        'company' => 'Brightside Studio',
                        'from' => '2019-06',
                        'to' => '2022-02',
                        'current' => false,
                        'description' => 'Shipped brand and product work for startups across fintech and health.',
                    ],
                ],
                'educations' => [
                    [
                        'institution' => 'Rhode Island School of Design',
                        'degree' => 'BFA',
                        'fieldOfStudy' => 'Graphic Design',
                    ],
                ],
                'projects' => [
                    [
                        'title' => 'Atlas Dashboard',
                        'description' => 'Analytics dashboard redesign that cut time-to-insight by 35%.',
                        'technologies' => ['Figma', 'React', 'Laravel'],
                        'url' => 'https://example.com/atlas',
                        'featured' => true,
                        'from' => '2023-01',
                        'to' => '2023-08',
                    ],
                    [
                        'title' => 'Harbor Mobile',
                        'description' => 'Consumer mobile app for local marketplace discovery.',
                        'technologies' => ['SwiftUI', 'API Design'],
                        'url' => 'https://example.com/harbor',
                        'from' => '2021-04',
                        'to' => '2021-11',
                    ],
                    [
                        'title' => 'Pulse Design System',
                        'description' => 'Shared component library adopted across three product squads.',
                        'technologies' => ['Design Tokens', 'Storybook'],
                        'from' => '2022-06',
                        'to' => '2022-12',
                    ],
                ],
                'skills' => [
                    ['name' => 'Product Design', 'category' => 'Design', 'level' => 'expert'],
                    ['name' => 'Design Systems', 'category' => 'Design', 'level' => 'expert'],
                    ['name' => 'Laravel', 'category' => 'Engineering', 'level' => 'advanced'],
                    ['name' => 'React', 'category' => 'Engineering', 'level' => 'advanced'],
                    ['name' => 'User Research', 'category' => 'Research', 'level' => 'advanced'],
                ],
                'languages' => [
                    ['name' => 'English', 'proficiency' => 'Native'],
                    ['name' => 'Spanish', 'proficiency' => 'Conversational'],
                ],
                'services' => [
                    [
                        'title' => 'Product design sprints',
                        'description' => 'Two-week discovery and UI delivery for early-stage products.',
                    ],
                    [
                        'title' => 'Design system audits',
                        'description' => 'Find gaps, define tokens, and align engineering handoff.',
                    ],
                ],
                'testimonials' => [
                    [
                        'quote' => 'Jane elevates every room she joins — sharp taste, clear communication, and shipping discipline.',
                        'author' => 'Alex Rivera',
                        'role' => 'VP Product',
                        'company' => 'Northwind Labs',
                    ],
                ],
                'certifications' => [
                    [
                        'name' => 'Google UX Certificate',
                        'issuer' => 'Google',
                        'date' => '2020-05',
                    ],
                ],
                'achievements' => [
                    [
                        'title' => 'Awwwards Honorable Mention',
                        'description' => 'Harbor Mobile marketing site',
                        'year' => '2022',
                    ],
                ],
                'availability' => [
                    'status' => 'available',
                    'message' => 'Open for select freelance projects in Q3.',
                    'rate' => null,
                ],
                'cta' => [
                    'label' => 'Hire me',
                    'url' => 'mailto:jane.doe@example.com',
                ],
                'sections_order' => [
                    'about', 'services', 'experiences', 'projects', 'skills',
                    'educations', 'testimonials', 'availability',
                ],
                'seo' => [
                    'meta_title' => 'Jane Doe — Product Designer',
                    'meta_description' => 'Portfolio of Jane Doe, product designer and full-stack builder.',
                ],
            ],
        );
    }
}
