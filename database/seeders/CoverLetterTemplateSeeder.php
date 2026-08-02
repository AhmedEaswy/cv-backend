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
                'preview' => 'images/templates/ats-classic.png',
                'description' => 'ATS-friendly cover letter matching the ATS Classic CV style, with English and Arabic support.',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'professional',
                'preview' => 'images/templates/professional.png',
                'description' => 'Professional cover letter layout with clean typography and bilingual support.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'serif-formal',
                'preview' => 'images/templates/serif-formal.svg',
                'description' => 'Traditional serif letterhead with centered name and double-rule divider.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'stripe-modern',
                'preview' => 'images/templates/stripe-modern.svg',
                'description' => 'Modern layout with a teal vertical brand stripe and clean sans body.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'editorial-masthead',
                'preview' => 'images/templates/editorial-masthead.svg',
                'description' => 'Newspaper-style oversized masthead with editorial typography.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'mono-tech',
                'preview' => 'images/templates/mono-tech.svg',
                'description' => 'Developer-focused monospace letter with a left meta stack.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'dual-tone',
                'preview' => 'images/templates/dual-tone.svg',
                'description' => 'Two-tone navy and soft-blue header band with white name treatment.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'elegant-rules',
                'preview' => 'images/templates/elegant-rules.svg',
                'description' => 'Quiet luxury letter with wide margins, hairline rules, and small-caps labels.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'banner-bold',
                'preview' => 'images/templates/banner-bold.svg',
                'description' => 'Full-width charcoal name banner with high-contrast contact row.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'compact-exec',
                'preview' => 'images/templates/compact-exec.svg',
                'description' => 'Dense executive letterhead with right-aligned date and corporate blue accents.',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            CoverLetterTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template,
            );
        }
    }
}
