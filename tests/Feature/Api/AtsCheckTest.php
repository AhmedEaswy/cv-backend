<?php

namespace Tests\Feature\Api;

use App\Models\AtsCheck;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AtsCheckTest extends TestCase
{
    use RefreshDatabase;

    private function strongUserData(): array
    {
        return [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'jobTitle' => 'Software Engineer',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => 'New York, NY',
            'summary' => 'Experienced software engineer with over ten years building reliable products.',
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'Laravel'],
                ['name' => 'Flutter'],
                ['name' => 'Dart'],
            ],
            'experiences' => [
                [
                    'position' => 'Senior Developer',
                    'company' => 'Acme Corp',
                    'from' => '2020-01',
                    'to' => '2024-01',
                    'description' => 'Developed and led backend services. Improved delivery speed across the platform.',
                ],
            ],
            'educations' => [
                [
                    'institution' => 'MIT',
                    'degree' => 'Bachelor',
                    'fieldOfStudy' => 'Computer Science',
                    'from' => '2010-09',
                    'to' => '2014-06',
                ],
            ],
        ];
    }

    public function test_structured_ats_check_returns_score(): void
    {
        $response = $this->postJson('/api/v1/cvs/ats-check', [
            'user_data' => $this->strongUserData(),
            'language' => 'en',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'result' => [
                    'score',
                    'grade',
                    'source',
                    'categories',
                    'checks',
                    'keywords',
                    'check_id',
                ],
            ]);

        $this->assertSame('structured', $response->json('result.source'));
        $this->assertNull($response->json('result.keywords'));
        $this->assertGreaterThanOrEqual(70, $response->json('result.score'));
        $this->assertDatabaseHas('ats_checks', [
            'id' => $response->json('result.check_id'),
            'source' => 'structured',
            'grade' => $response->json('result.grade'),
        ]);
        $this->assertSame(1, AtsCheck::count());
    }

    public function test_structured_ats_check_with_job_description_includes_keywords(): void
    {
        $response = $this->postJson('/api/v1/cvs/ats-check', [
            'user_data' => $this->strongUserData(),
            'job_description' => 'We need a Flutter and Dart engineer with Laravel and Firebase experience.',
        ]);

        $response->assertOk();
        $this->assertIsArray($response->json('result.keywords'));
        $this->assertArrayHasKey('matched', $response->json('result.keywords'));
        $this->assertArrayHasKey('missing', $response->json('result.keywords'));
        $this->assertArrayHasKey('coverage_percent', $response->json('result.keywords'));
        $this->assertArrayHasKey('keyword_fit', $response->json('result.categories'));
        $this->assertContains('flutter', $response->json('result.keywords.matched'));
        $this->assertDatabaseHas('ats_checks', [
            'id' => $response->json('result.check_id'),
            'has_job_description' => 1,
        ]);
    }

    public function test_structured_ats_check_via_profile_id(): void
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'name' => 'Test CV',
            'language' => 'en',
            'info' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'jobTitle' => 'Software Engineer',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'address' => 'NY',
                'summary' => 'Experienced software engineer with over ten years of product delivery.',
                'skills' => [
                    ['name' => 'PHP'],
                    ['name' => 'Laravel'],
                    ['name' => 'MySQL'],
                ],
            ],
            'experiences' => [
                [
                    'position' => 'Senior Developer',
                    'name' => 'Acme',
                    'from' => '2020-01',
                    'description' => 'Developed APIs and improved system reliability for customers.',
                ],
            ],
            'educations' => [
                [
                    'institution' => 'MIT',
                    'degree' => 'BSc',
                    'fieldOfStudy' => 'CS',
                ],
            ],
        ]);

        $response = $this->postJson('/api/v1/cvs/ats-check', [
            'profile_id' => $profile->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('result.source', 'structured');

        $this->assertDatabaseHas('ats_checks', [
            'id' => $response->json('result.check_id'),
            'profile_id' => $profile->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_structured_ats_check_requires_profile_or_user_data(): void
    {
        $response = $this->postJson('/api/v1/cvs/ats-check', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_pdf_upload_ats_check(): void
    {
        $pdfPath = $this->makeTextPdfFixture(
            "John Doe\njohn@example.com\n+1234567890\nSummary\nExperienced engineer\nExperience\nDeveloped software\nEducation\nSkills\nPHP Laravel Flutter"
        );

        $response = $this->post('/api/v1/cvs/ats-check/upload', [
            'file' => new UploadedFile($pdfPath, 'resume.pdf', 'application/pdf', null, true),
            'job_description' => 'Looking for PHP Laravel developer with Flutter skills.',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('result.source', 'pdf');

        $this->assertNotNull($response->json('result.keywords'));
        $this->assertGreaterThan(0, $response->json('result.score'));
        $this->assertDatabaseHas('ats_checks', [
            'id' => $response->json('result.check_id'),
            'source' => 'pdf',
            'pdf_original_name' => 'resume.pdf',
        ]);

        @unlink($pdfPath);
    }

    public function test_pdf_upload_rejects_non_pdf(): void
    {
        $response = $this->post('/api/v1/cvs/ats-check/upload', [
            'file' => UploadedFile::fake()->create('resume.txt', 10, 'text/plain'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Build a minimal text PDF smalot/pdfparser can read.
     */
    private function makeTextPdfFixture(string $text): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ats-fixture-'.uniqid('', true).'.pdf';

        // Escape parentheses for PDF string literal
        $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $safe = preg_replace("/\r\n|\r|\n/", ' ', $safe) ?? $safe;

        $contentStream = "BT /F1 11 Tf 50 750 Td ({$safe}) Tj ET";
        $contentLength = strlen($contentStream);

        $objects = [];
        $objects[] = '1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj';
        $objects[] = '2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj';
        $objects[] = '3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources<< /Font<< /F1 5 0 R >> >> >>endobj';
        $objects[] = "4 0 obj<< /Length {$contentLength} >>stream\n{$contentStream}\nendstream\nendobj";
        $objects[] = '5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefPos = strlen($pdf);
        $count = count($offsets);
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer<< /Size {$count} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPos}\n%%EOF\n";

        file_put_contents($path, $pdf);

        return $path;
    }
}
