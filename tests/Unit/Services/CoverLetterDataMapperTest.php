<?php

namespace Tests\Unit\Services;

use App\Models\CoverLetter;
use App\Services\CoverLetterDataMapper;
use Tests\TestCase;

class CoverLetterDataMapperTest extends TestCase
{
    private CoverLetterDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new CoverLetterDataMapper();
    }

    public function test_format_cover_letter_response_handles_json_string_info(): void
    {
        $coverLetter = new CoverLetter([
            'name' => 'Test',
            'language' => 'en',
            'info' => json_encode([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'body' => "Line one\n\nLine two",
            ]),
        ]);

        $data = $this->mapper->formatCoverLetterResponse($coverLetter);

        $this->assertSame('John', $data['user_data']['firstName']);
        $this->assertSame('Doe', $data['user_data']['lastName']);
        $this->assertSame("Line one\n\nLine two", $data['user_data']['body']);
    }

    public function test_format_cover_letter_response_handles_array_body(): void
    {
        $coverLetter = new CoverLetter([
            'name' => 'Test',
            'language' => 'en',
            'info' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'body' => ['Paragraph one', 'Paragraph two'],
            ],
        ]);

        $data = $this->mapper->formatCoverLetterResponse($coverLetter);

        $this->assertSame("Paragraph one\n\nParagraph two", $data['user_data']['body']);
    }

    public function test_map_user_data_to_cover_letter_normalizes_nested_json_string(): void
    {
        $mapped = $this->mapper->mapUserDataToCoverLetter(json_decode(json_encode([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'body' => ['Paragraph one', 'Paragraph two'],
        ]), true));

        $this->assertSame('John', $mapped['info']['firstName']);
        $this->assertSame("Paragraph one\n\nParagraph two", $mapped['info']['body']);
    }

    public function test_malformed_info_does_not_break_template_rendering(): void
    {
        $coverLetter = new CoverLetter([
            'name' => 'Test',
            'language' => 'en',
            'info' => json_encode([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@example.com',
                'body' => 'Hello world',
            ]),
        ]);

        $data = $this->mapper->formatCoverLetterResponse($coverLetter);

        $html = view('templates.cover-letter.professional', ['coverLetter' => $data])->render();

        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('Hello world', $html);
    }
}
