<?php

namespace Database\Seeders;

use App\Models\CoverLetterTemplate;
use Illuminate\Database\Seeder;

class CoverLetterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'ats-classic',
                'preview' => 'cover-letter-templates/ats-classic.svg',
                'description' => 'ATS-friendly cover letter matching the ATS Classic CV style, with English and Arabic support.',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'professional',
                'preview' => 'cover-letter-templates/professional.svg',
                'description' => 'Professional cover letter layout with clean typography and bilingual support.',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            CoverLetterTemplate::create($template);
        }
    }
}
