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
        // Get existing users or create a sample user
        $users = User::all();

        if ($users->isEmpty()) {
            $user = User::create([
                'name' => 'John Doe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'type' => \App\Enums\UserType::USER,
            ]);
            $users = collect([$user]);
        }

        $sampleProfiles = [
            [
                'user_id' => $users->first()->id,
                'interests' => ['Web Development', 'Machine Learning', 'Photography'],
                'languages' => [
                    ['language' => 'English', 'level' => 'Native'],
                    ['language' => 'Spanish', 'level' => 'Intermediate'],
                ],
                'info' => [
                    'address' => '123 Main St, City, Country',
                    'phone' => '+1-555-0123',
                    'email' => 'john@example.com',
                    'website' => 'https://johndoe.dev',
                    'summary' => 'Passionate full-stack developer with 5+ years of experience',
                    'bio' => 'I love creating amazing web applications and solving complex problems.',
                    'portfolio' => 'https://johndoe.dev/portfolio',
                    'military_status' => 'Not applicable',
                    'ready_to_relocate' => true,
                ],
                'experiences' => [
                    [
                        'name' => 'Tech Corp',
                        'location' => 'San Francisco, CA',
                        'position' => 'Senior Developer',
                        'description' => 'Led development of multiple web applications',
                        'from' => '2022-01-01',
                        'to' => null,
                        'currentlyWorkingHere' => true,
                    ],
                    [
                        'name' => 'StartupXYZ',
                        'location' => 'Remote',
                        'position' => 'Full Stack Developer',
                        'description' => 'Built and maintained web applications',
                        'from' => '2020-06-01',
                        'to' => '2021-12-31',
                        'currentlyWorkingHere' => false,
                    ],
                ],
                'projects' => [
                    [
                        'name' => 'E-commerce Platform',
                        'description' => 'A full-featured e-commerce platform built with Laravel and Vue.js',
                        'url' => 'https://github.com/johndoe/ecommerce',
                        'from' => '2023-01-01',
                        'to' => '2023-06-01',
                    ],
                    [
                        'name' => 'Task Management App',
                        'description' => 'A collaborative task management application',
                        'url' => 'https://github.com/johndoe/taskmanager',
                        'from' => '2022-08-01',
                        'to' => '2022-12-01',
                    ],
                ],
                'educations' => [
                    [
                        'institution' => 'University of Technology',
                        'degree' => 'Bachelor of Science',
                        'fieldOfStudy' => 'Computer Science',
                        'description' => 'Graduated with honors',
                        'from' => '2016-09-01',
                        'to' => '2020-05-31',
                    ],
                ],
            ],
        ];

        foreach ($sampleProfiles as $profile) {
            Profile::create($profile);
        }
    }
}
