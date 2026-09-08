<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
                'preview' => 'images/cv-templates/modern-professional.svg',
                'description' => 'A clean and modern template perfect for tech professionals and developers.',
                'is_active' => true,
                'is_default' => true,
                'supports_image' => false,
            ],
            [
                'name' => 'office-manager',
                'preview' => 'images/cv-templates/office-manager.svg',
                'description' => 'A template for office managers and administrators.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => false,
            ],
            [
                'name' => 'ats-classic',
                'preview' => 'images/cv-templates/ats-classic.svg',
                'description' => 'ATS-friendly single-column layout with clear sections, optimized for English and Arabic.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => false,
            ],
            [
                'name' => 'portrait-modern',
                'preview' => 'images/cv-templates/portrait-modern.svg',
                'description' => 'Airy teal-accent CV with a circular portrait photo top-right.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'sidebar-slate',
                'preview' => 'images/cv-templates/sidebar-slate.svg',
                'description' => 'Dark slate sidebar with photo, contact, and skills; white main column.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'metro-grid',
                'preview' => 'images/cv-templates/metro-grid.svg',
                'description' => 'Magazine-style grid layout with large photo beside the name block.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'midnight-banner',
                'preview' => 'images/cv-templates/midnight-banner.svg',
                'description' => 'Near-black header banner with inset photo and gold accents.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'coral-split',
                'preview' => 'images/cv-templates/coral-split.svg',
                'description' => 'Warm coral and cream split header with rounded portrait photo.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'forest-folio',
                'preview' => 'images/cv-templates/forest-folio.svg',
                'description' => 'Earth-toned green sidebar folio with serif headings and soft cream paper.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
            [
                'name' => 'ink-editorial',
                'preview' => 'images/cv-templates/ink-editorial.svg',
                'description' => 'Black-and-white editorial masthead with a small formal portrait.',
                'is_active' => true,
                'is_default' => false,
                'supports_image' => true,
            ],
        ];

        foreach ($templates as $data) {
            // Insert missing templates only — never overwrite existing rows wholesale.
            $template = Template::firstOrCreate(
                ['name' => $data['name']],
                $data,
            );

            // Repair broken preview paths (e.g. old storage/*.svg that were never shipped).
            if ($this->previewMissing($template->preview) && ! $this->previewMissing($data['preview'])) {
                $template->update(['preview' => $data['preview']]);
            }
        }
    }

    private function previewMissing(?string $preview): bool
    {
        if (! $preview) {
            return true;
        }

        if (str_starts_with($preview, 'http://') || str_starts_with($preview, 'https://')) {
            return false;
        }

        if (str_starts_with($preview, 'images/')) {
            return ! is_file(public_path($preview));
        }

        return ! Storage::disk('public')->exists($preview);
    }
}
