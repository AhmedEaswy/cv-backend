<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            ],
            [
                'name' => 'office-manager',
                'preview' => 'templates/previews/office-manager.svg',
                'description' => 'A template for office managers and administrators.',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'ats-classic',
                'preview' => 'templates/previews/ats-classic.svg',
                'description' => 'ATS-friendly single-column layout with clear sections, optimized for English and Arabic.',
                'is_active' => true,
                'is_default' => false,
            ],
            // [
            //     'name' => 'creative-designer',
            //     'preview' => 'templates/previews/creative-designer.jpg',
            //     'description' => 'A colorful and creative template ideal for designers and artists.',
            //     'is_active' => false,
            // ],
            // [
            //     'name' => 'executive-corporate',
            //     'preview' => 'templates/previews/executive-corporate.jpg',
            //     'description' => 'A formal and professional template for executives and business leaders.',
            //     'is_active' => false,
            // ],
            // [
            //     'name' => 'minimalist-clean',
            //     'preview' => 'templates/previews/minimalist-clean.jpg',
            //     'description' => 'A simple and elegant template focusing on content and readability.',
            //     'is_active' => false,
            // ],
            // [
            //     'name' => 'academic-scholar',
            //     'preview' => 'templates/previews/academic-scholar.jpg',
            //     'description' => 'A traditional template perfect for academics and researchers.',
            //     'is_active' => false,
            // ],
        ];

        foreach ($templates as $template) {
            Template::create($template);
        }
    }
}
