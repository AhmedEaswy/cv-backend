<?php

namespace Tests\Feature;

use App\Models\CoverLetter;
use App\Models\User;
use App\Services\CoverLetterDataMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverLetterTemplateRenderTest extends TestCase
{
    use RefreshDatabase;

    private CoverLetterDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new CoverLetterDataMapper();
    }

    private function createTestCoverLetter(string $language = 'en'): CoverLetter
    {
        $user = User::factory()->create();

        return CoverLetter::create([
            'user_id' => $user->id,
            'name' => 'Test Cover Letter',
            'language' => $language,
            'info' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St, City',
                'jobTitle' => 'Software Engineer',
                'recipientName' => 'Jane Smith',
                'recipientTitle' => 'Hiring Manager',
                'recipientCompany' => 'Acme Corp',
                'subject' => 'Application for Software Engineer Position',
                'body' => "Dear Hiring Manager,\n\nI am writing to express my interest in the Software Engineer role.\n\nThank you for your consideration.",
                'closing' => 'Sincerely',
            ],
        ]);
    }

    public function test_ats_classic_cover_letter_template_renders(): void
    {
        $coverLetter = $this->createTestCoverLetter();
        $data = $this->mapper->formatCoverLetterResponse($coverLetter);

        $html = view('templates.cover-letter.ats-classic', ['coverLetter' => $data])->render();

        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('john@example.com', $html);
        $this->assertStringContainsString('Jane Smith', $html);
        $this->assertStringContainsString('Application for Software Engineer Position', $html);
        $this->assertStringContainsString('Software Engineer role', $html);
    }

    public function test_professional_cover_letter_template_renders(): void
    {
        $coverLetter = $this->createTestCoverLetter();
        $data = $this->mapper->formatCoverLetterResponse($coverLetter);

        $html = view('templates.cover-letter.professional', ['coverLetter' => $data])->render();

        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('Acme Corp', $html);
        $this->assertStringContainsString('professional-header', $html);
        $this->assertStringContainsString('signature-rule', $html);
        $this->assertStringNotContainsString('sender-block', $html);
    }

    public function test_cover_letter_renders_rtl_for_arabic(): void
    {
        $coverLetter = $this->createTestCoverLetter('ar');
        $data = $this->mapper->formatCoverLetterResponse($coverLetter);
        app()->setLocale('ar');

        $html = view('templates.cover-letter.ats-classic', ['coverLetter' => $data])->render();

        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('الموضوع', $html);
    }
}
