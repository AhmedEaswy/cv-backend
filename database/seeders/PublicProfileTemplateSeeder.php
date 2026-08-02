<?php

namespace Database\Seeders;

use App\Models\PublicProfileTemplate;
use Illuminate\Database\Seeder;

class PublicProfileTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'minimal-folio',
                'preview' => 'images/public-profile-templates/minimal-folio.png',
                'description' => 'Clean white-space portfolio with thin typography and a single-column layout.',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'dark-terminal',
                'preview' => 'images/public-profile-templates/dark-terminal.png',
                'description' => 'Developer-style dark terminal theme with monospace type and green accents.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'editorial-serif',
                'preview' => 'images/public-profile-templates/editorial-serif.png',
                'description' => 'Magazine-inspired editorial layout with large serif headlines and a cream paper feel.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'bold-poster',
                'preview' => 'images/public-profile-templates/bold-poster.png',
                'description' => 'High-contrast poster blocks with huge display type and asymmetric media.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'soft-pastel',
                'preview' => 'images/public-profile-templates/soft-pastel.png',
                'description' => 'Friendly pastel washes, rounded section bands, and soft typography.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'corporate-split',
                'preview' => 'images/public-profile-templates/corporate-split.png',
                'description' => 'Professional split layout with a sticky navy nav rail and structured content.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'timeline-vertical',
                'preview' => 'images/public-profile-templates/timeline-vertical.png',
                'description' => 'Centered vertical timeline spine for career and project milestones.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'gallery-masonry',
                'preview' => 'images/public-profile-templates/gallery-masonry.png',
                'description' => 'Dark gallery-first masonry grid where projects dominate the page.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'cardless-gradient',
                'preview' => 'images/public-profile-templates/cardless-gradient.png',
                'description' => 'Full-bleed gradient hero with glass panels for a modern SaaS look.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'classic-centered',
                'preview' => 'images/public-profile-templates/classic-centered.png',
                'description' => 'Traditional centered portfolio stack with serif display and calm spacing.',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            PublicProfileTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template,
            );
        }
    }
}
