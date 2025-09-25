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
                'name' => 'Modern Professional',
                'preview' => 'modern-professional.jpg',
                'description' => 'A clean and modern template perfect for tech professionals and developers.',
            ],
            [
                'name' => 'Creative Designer',
                'preview' => 'creative-designer.jpg',
                'description' => 'A colorful and creative template ideal for designers and artists.',
            ],
            [
                'name' => 'Executive Corporate',
                'preview' => 'executive-corporate.jpg',
                'description' => 'A formal and professional template for executives and business leaders.',
            ],
            [
                'name' => 'Minimalist Clean',
                'preview' => 'minimalist-clean.jpg',
                'description' => 'A simple and elegant template focusing on content and readability.',
            ],
            [
                'name' => 'Academic Scholar',
                'preview' => 'academic-scholar.jpg',
                'description' => 'A traditional template perfect for academics and researchers.',
            ],
        ];

        foreach ($templates as $template) {
            Template::create($template);
        }
    }
}
