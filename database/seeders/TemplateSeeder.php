<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'modern-professional',
                'preview' => 'templates/previews/modern-professional.svg',
                'description' => 'A clean and modern template perfect for tech professionals and developers.',
                'is_active' => true,
                'is_default' => true,
                'supports_image' => false,
            ],
            [
                'name' => 'office-manager',
                'preview' => 'templates/previews/office-manager.svg',
                'description' => 'A template for office managers and administrators.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => false,
            ],
            [
                'name' => 'ats-classic',
                'preview' => 'templates/previews/ats-classic.svg',
                'description' => 'ATS-friendly single-column layout with clear sections, optimized for English and Arabic.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => false,
            ],
            [
                'name' => 'portrait-modern',
                'preview' => 'templates/previews/portrait-modern.svg',
                'description' => 'Airy teal-accent CV with a circular portrait photo top-right.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'sidebar-slate',
                'preview' => 'templates/previews/sidebar-slate.svg',
                'description' => 'Dark slate sidebar with photo, contact, and skills; white main column.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'metro-grid',
                'preview' => 'templates/previews/metro-grid.svg',
                'description' => 'Magazine-style grid layout with large photo beside the name block.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'midnight-banner',
                'preview' => 'templates/previews/midnight-banner.svg',
                'description' => 'Near-black header banner with inset photo and gold accents.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'coral-split',
                'preview' => 'templates/previews/coral-split.svg',
                'description' => 'Warm coral and cream split header with rounded portrait photo.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'forest-folio',
                'preview' => 'templates/previews/forest-folio.svg',
                'description' => 'Earth-toned green sidebar folio with serif headings and soft cream paper.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'ink-editorial',
                'preview' => 'templates/previews/ink-editorial.svg',
                'description' => 'Black-and-white editorial masthead with a small formal portrait.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['name' => $template['name']],
                $template,
            );
        }
    }
}
