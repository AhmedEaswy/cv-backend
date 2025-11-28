<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first or create a user manually.');
            return;
        }

        $user = $users->first();

        $sampleProfiles = [
            [
                'user_id' => $user->id,
                'name' => 'My Professional CV',
                'language' => 'en',
                'sections_order' => [
                    'Personal Information',
                    'Skills',
                    'Experience',
                    'Education',
                    'Projects',
                    'Languages',
                    'Interests',
                ],
                'info' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'jobTitle' => 'Senior Full Stack Developer',
                    'email' => 'john.doe@example.com',
                    'address' => '123 Tech Street, San Francisco, CA 94105, USA',
                    'portfolioUrl' => 'https://johndoe.dev',
                    'phone' => '+1-555-123-4567',
                    'summary' => 'Experienced full-stack developer with 5+ years of expertise in web and mobile app development. Passionate about creating beautiful, performant, and user-friendly applications. Strong background in Laravel, React, and Flutter development.',
                    'birthdate' => '1990-05-15',
                    'skills' => [
                        ['name' => 'Laravel'],
                        ['name' => 'PHP'],
                        ['name' => 'JavaScript'],
                        ['name' => 'React'],
                        ['name' => 'Vue.js'],
                        ['name' => 'Flutter'],
                        ['name' => 'Dart'],
                        ['name' => 'MySQL'],
                        ['name' => 'PostgreSQL'],
                        ['name' => 'Git'],
                        ['name' => 'Docker'],
                    ],
                ],
                'languages' => [
                    [
                        'language' => 'English',
                        'level' => 'native',
                    ],
                    [
                        'language' => 'Spanish',
                        'level' => 'fluent',
                    ],
                    [
                        'language' => 'French',
                        'level' => 'intermediate',
                    ],
                ],
                'interests' => [
                    [
                        'interest' => 'Open Source Contributions',
                    ],
                    [
                        'interest' => 'Web Development',
                    ],
                    [
                        'interest' => 'Mobile UI/UX Design',
                    ],
                    [
                        'interest' => 'Photography',
                    ],
                    [
                        'interest' => 'Reading Tech Blogs',
                    ],
                ],
                'experiences' => [
                    [
                        'position' => 'Senior Full Stack Developer',
                        'name' => 'Tech Innovations Inc.',
                        'location' => 'San Francisco, CA',
                        'description' => 'Lead development of enterprise web and mobile applications serving 100K+ users. Architected scalable solutions, mentored junior developers, and collaborated with cross-functional teams. Improved application performance by 40% through code optimization and caching strategies.',
                        'from' => '2021-03',
                        'to' => null,
                        'currentlyWorkingHere' => true,
                    ],
                    [
                        'position' => 'Full Stack Developer',
                        'name' => 'StartupXYZ',
                        'location' => 'San Francisco, CA',
                        'description' => 'Developed and maintained multiple web applications from scratch. Implemented clean architecture patterns, RESTful API integration, and real-time features. Delivered 3 major application releases on time.',
                        'from' => '2019-06',
                        'to' => '2021-02',
                        'currentlyWorkingHere' => false,
                    ],
                    [
                        'position' => 'Junior Web Developer',
                        'name' => 'Digital Solutions LLC',
                        'location' => 'Oakland, CA',
                        'description' => 'Built responsive web applications using Laravel and Vue.js. Collaborated with designers and backend developers to implement user-facing features. Participated in code reviews and agile development processes.',
                        'from' => '2017-08',
                        'to' => '2019-05',
                        'currentlyWorkingHere' => false,
                    ],
                ],
                'projects' => [
                    [
                        'name' => 'E-Commerce Platform',
                        'description' => 'A full-featured e-commerce platform built with Laravel and Vue.js. Features include user authentication, product catalog, shopping cart, payment integration (Stripe), order tracking, and admin dashboard.',
                        'url' => 'https://github.com/johndoe/ecommerce',
                        'from' => '2023-01',
                        'to' => '2023-06',
                    ],
                    [
                        'name' => 'Task Management App',
                        'description' => 'Cross-platform task management application with team collaboration features. Implemented real-time synchronization, offline mode, and intuitive UI/UX. Built with Flutter and Laravel backend.',
                        'url' => 'https://github.com/johndoe/task-manager',
                        'from' => '2022-08',
                        'to' => '2022-12',
                    ],
                    [
                        'name' => 'Portfolio Website',
                        'description' => 'Personal portfolio website showcasing projects and skills. Built with React and Next.js, featuring responsive design and smooth animations.',
                        'url' => 'https://johndoe.dev',
                        'from' => '2022-01',
                        'to' => '2022-03',
                    ],
                ],
                'educations' => [
                    [
                        'institution' => 'University of California, Berkeley',
                        'degree' => 'Bachelor of Science',
                        'fieldOfStudy' => 'Computer Science',
                        'description' => 'Graduated magna cum laude. Specialized in software engineering and web application development. Relevant coursework included Data Structures, Algorithms, Database Systems, and Software Engineering Principles.',
                        'from' => '2013-09',
                        'to' => '2017-05',
                    ],
                    [
                        'institution' => 'Stanford Continuing Studies',
                        'degree' => 'Certificate',
                        'fieldOfStudy' => 'Mobile App Development',
                        'description' => 'Advanced course covering modern mobile development practices, architecture patterns, and performance optimization using Flutter and React Native.',
                        'from' => '2019-01',
                        'to' => '2019-06',
                    ],
                ],
            ],
            [
                'user_id' => $user->id,
                'name' => 'Developer Resume - Arabic',
                'language' => 'ar',
                'sections_order' => [
                    'Personal Information',
                    'Skills',
                    'Experience',
                    'Education',
                ],
                'info' => [
                    'firstName' => 'Ahmed',
                    'lastName' => 'Ali',
                    'jobTitle' => 'Laravel Developer',
                    'email' => 'ahmed.ali@example.com',
                    'address' => 'Cairo, Egypt',
                    'phone' => '+20-100-123-4567',
                    'summary' => 'Experienced Laravel developer with expertise in building scalable web applications.',
                    'skills' => [
                        ['name' => 'Laravel'],
                        ['name' => 'PHP'],
                        ['name' => 'MySQL'],
                        ['name' => 'JavaScript'],
                    ],
                ],
                'languages' => [
                    [
                        'language' => 'Arabic',
                        'level' => 'native',
                    ],
                    [
                        'language' => 'English',
                        'level' => 'fluent',
                    ],
                ],
                'interests' => [],
                'experiences' => [
                    [
                        'position' => 'Laravel Developer',
                        'name' => 'Tech Company',
                        'location' => 'Cairo, Egypt',
                        'description' => 'Developed and maintained web applications using Laravel framework.',
                        'from' => '2022-01',
                        'to' => null,
                        'currentlyWorkingHere' => true,
                    ],
                ],
                'projects' => [],
                'educations' => [
                    [
                        'institution' => 'Cairo University',
                        'degree' => 'Bachelor of Science',
                        'fieldOfStudy' => 'Computer Science',
                        'description' => 'Graduated with honors in Computer Science.',
                        'from' => '2018-09',
                        'to' => '2022-06',
                    ],
                ],
            ],
        ];

        foreach ($sampleProfiles as $profile) {
            Profile::create($profile);
        }

        $this->command->info('Profile seeder completed successfully.');
    }
}
