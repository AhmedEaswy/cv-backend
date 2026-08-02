<?php

namespace Database\Seeders;

use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoverLetterSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@app.com')->first() ?? User::first();

        if (! $user) {
            $this->command->warn('No users found. Skipping cover letter seeder.');

            return;
        }

        $defaultTemplate = CoverLetterTemplate::where('is_default', true)->first()
            ?? CoverLetterTemplate::where('is_active', true)->first();

        if (! $defaultTemplate) {
            $this->command->warn('No cover letter templates found. Run CoverLetterTemplateSeeder first.');

            return;
        }

        $samples = [
            [
                'name' => 'Software Engineer Application',
                'language' => 'en',
                'is_public' => true,
                'cover_letter_template_id' => $defaultTemplate->id,
                'info' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'jobTitle' => 'Senior Full Stack Developer',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1-555-123-4567',
                    'address' => 'San Francisco, CA',
                    'companyName' => 'Acme Corp',
                    'recipientName' => 'Jane Smith',
                    'recipientTitle' => 'Hiring Manager',
                    'recipientCompany' => 'Acme Corp',
                    'subject' => 'Application for Software Engineer',
                    'body' => "I am writing to express my interest in the Software Engineer position at Acme Corp. With over five years of experience building scalable web applications with Laravel and React, I am confident I can contribute to your team.\n\nIn my current role I led features used by 100K+ users and improved performance by 40%. I would welcome the chance to discuss how my background fits your needs.",
                    'closing' => 'Sincerely',
                ],
            ],
            [
                'name' => 'Arabic Cover Letter Sample',
                'language' => 'ar',
                'is_public' => true,
                'cover_letter_template_id' => $defaultTemplate->id,
                'info' => [
                    'firstName' => 'أحمد',
                    'lastName' => 'علي',
                    'jobTitle' => 'مطور Laravel',
                    'email' => 'ahmed.ali@example.com',
                    'phone' => '+20-100-123-4567',
                    'address' => 'القاهرة، مصر',
                    'recipientCompany' => 'شركة التقنية',
                    'subject' => 'طلب وظيفة مطور ويب',
                    'body' => 'أتقدم بطلبي لشغل وظيفة مطور ويب. لدي خبرة في بناء تطبيقات قابلة للتوسع باستخدام Laravel، وأود المساهمة في نجاح فريقكم.',
                    'closing' => 'مع أطيب التحيات',
                ],
            ],
        ];

        foreach ($samples as $sample) {
            CoverLetter::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $sample['name'],
                ],
                array_merge($sample, ['user_id' => $user->id]),
            );
        }

        $this->command->info('Cover letter seeder completed successfully.');
    }
}
