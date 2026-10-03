<?php

namespace Database\Seeders;

use App\Enums\HelpArticleKind;
use App\Models\HelpArticle;
use App\Models\ProductTour;
use App\Models\ProductTourStep;
use Illuminate\Database\Seeder;

class LocalSupportContentSeeder extends Seeder
{
    /**
     * Demo product tour and published FAQ for local development only.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('LocalSupportContentSeeder skipped (APP_ENV is not local).');

            return;
        }

        $tour = ProductTour::query()->updateOrCreate(
            ['key' => 'portal_intro'],
            [
                'is_enabled' => true,
                'sort_order' => 1,
            ],
        );

        $steps = [
            [
                'sort_order' => 1,
                'title' => ['en' => 'Dashboard'],
                'body' => ['en' => 'Your home base — recent CVs, cover letters, and quick actions.'],
            ],
            [
                'sort_order' => 2,
                'title' => ['en' => 'CVs'],
                'body' => ['en' => 'Create, edit, and export ATS-friendly CVs with live preview.'],
            ],
            [
                'sort_order' => 3,
                'title' => ['en' => 'Cover letters'],
                'body' => ['en' => 'Pair cover letters with your CVs and print to PDF.'],
            ],
            [
                'sort_order' => 4,
                'title' => ['en' => 'Public profile'],
                'body' => ['en' => 'Share a polished link with optional contact form — separate from support tickets.'],
            ],
            [
                'sort_order' => 5,
                'title' => ['en' => 'Help'],
                'body' => ['en' => 'Replay this tour anytime from Help.'],
            ],
        ];

        foreach ($steps as $step) {
            ProductTourStep::query()->updateOrCreate(
                [
                    'product_tour_id' => $tour->id,
                    'sort_order' => $step['sort_order'],
                ],
                [
                    'title' => $step['title'],
                    'body' => $step['body'],
                    'is_enabled' => true,
                ],
            );
        }

        HelpArticle::query()->updateOrCreate(
            ['slug' => 'how-do-i-export-my-cv'],
            [
                'kind' => HelpArticleKind::Faq,
                'category' => 'getting-started',
                'title' => ['en' => 'How do I export my CV?'],
                'body' => ['en' => 'Open a CV from the portal, use the print or PDF action in the editor toolbar, and download or print from your browser.'],
                'is_published' => true,
                'sort_order' => 1,
            ],
        );
    }
}
