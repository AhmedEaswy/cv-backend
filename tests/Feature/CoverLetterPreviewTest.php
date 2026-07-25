<?php

namespace Tests\Feature;

use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverLetterPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_cover_letter_preview_renders_selected_template(): void
    {
        $user = User::factory()->create();
        $template = CoverLetterTemplate::create([
            'name' => 'ats-classic',
            'is_active' => true,
            'is_default' => true,
        ]);

        $coverLetter = CoverLetter::create([
            'user_id' => $user->id,
            'cover_letter_template_id' => $template->id,
            'name' => 'Cover Letter Preview',
            'language' => 'en',
            'info' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@example.com',
                'subject' => 'Application for Software Engineer',
                'body' => 'I am interested in this role.',
            ],
        ]);

        $response = $this->get(route('cover-letter.preview', [
            'id' => $coverLetter->id,
            'template_id' => $template->id,
        ]));

        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertSee('Application for Software Engineer');
    }
}
