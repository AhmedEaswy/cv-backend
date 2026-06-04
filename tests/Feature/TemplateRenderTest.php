<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use App\Services\CVDataMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateRenderTest extends TestCase
{
    use RefreshDatabase;

    private CVDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new CVDataMapper();
    }

    private function createTestProfile(): Profile
    {
        $user = User::factory()->create();

        return Profile::create([
            'user_id' => $user->id,
            'name' => 'Test CV',
            'language' => 'en',
            'info' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'jobTitle' => 'Software Engineer',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St, City',
                'portfolioUrl' => 'https://john.dev',
                'summary' => 'Experienced software engineer with 10+ years.',
                'skills' => [
                    ['name' => 'PHP'],
                    ['name' => 'Laravel'],
                    ['name' => 'JavaScript'],
                ],
            ],
            'experiences' => [
                [
                    'position' => 'Senior Developer',
                    'name' => 'Acme Corp',
                    'location' => 'New York',
                    'description' => 'Led backend development team.',
                    'from' => '2020-01',
                    'to' => '2024-01',
                    'currentlyWorkingHere' => false,
                ],
                [
                    'position' => 'CTO',
                    'name' => 'Startup Inc',
                    'location' => 'San Francisco',
                    'description' => 'Leading tech strategy.',
                    'from' => '2024-02',
                    'to' => null,
                    'currentlyWorkingHere' => true,
                ],
            ],
            'educations' => [
                [
                    'institution' => 'MIT',
                    'degree' => 'Bachelor of Science',
                    'fieldOfStudy' => 'Computer Science',
                    'from' => '2010-09',
                    'to' => '2014-06',
                    'description' => 'Graduated with honors.',
                ],
            ],
            'projects' => [
                [
                    'name' => 'Open Source CMS',
                    'description' => 'Built a headless CMS.',
                    'url' => 'https://github.com/john/cms',
                    'from' => '2022-01',
                    'to' => '2023-01',
                ],
            ],
            'languages' => [
                ['language' => 'English', 'level' => 'native'],
                ['language' => 'French', 'level' => 'advanced'],
            ],
            'interests' => [
                ['interest' => 'Open Source'],
                ['interest' => 'Photography'],
            ],
        ]);
    }

    public function test_modern_professional_template_renders(): void
    {
        $profile = $this->createTestProfile();
        $cvData = $this->mapper->formatProfileResponse($profile);

        $view = view('templates.cv.modern-professional', ['cv' => $cvData]);

        $html = $view->render();

        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('Software Engineer', $html);
        $this->assertStringContainsString('john@example.com', $html);
        $this->assertStringContainsString('Experienced software engineer', $html);
        $this->assertStringContainsString('Acme Corp', $html);
        $this->assertStringNotContainsString('@dd', $html);
    }

    public function test_office_manager_template_renders(): void
    {
        $profile = $this->createTestProfile();
        $cvData = $this->mapper->formatProfileResponse($profile);

        $view = view('templates.cv.office-manager', ['cv' => $cvData]);

        $html = $view->render();

        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('Software Engineer', $html);
        $this->assertStringContainsString('john@example.com', $html);
        $this->assertStringContainsString('Acme Corp', $html);
        $this->assertStringNotContainsString('@dd', $html);
    }

    public function test_templates_handle_missing_optional_data(): void
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'name' => 'Minimal CV',
            'language' => 'en',
            'info' => [
                'firstName' => 'Minimal',
                'lastName' => 'User',
            ],
        ]);

        $cvData = $this->mapper->formatProfileResponse($profile);

        $viewModern = view('templates.cv.modern-professional', ['cv' => $cvData]);
        $htmlModern = $viewModern->render();
        $this->assertStringContainsString('Minimal User', $htmlModern);

        $viewOffice = view('templates.cv.office-manager', ['cv' => $cvData]);
        $htmlOffice = $viewOffice->render();
        $this->assertStringContainsString('Minimal User', $htmlOffice);
    }

    public function test_template_layout_component_renders(): void
    {
        $user = User::factory()->create(['active' => true]);
        $profile = Profile::create([
            'user_id' => $user->id,
            'name' => 'Component Test',
            'language' => 'en',
            'info' => ['firstName' => 'Component', 'lastName' => 'Test'],
        ]);

        $cvData = $this->mapper->formatProfileResponse($profile);

        // Render the component via a child template which uses it
        $html = view('templates.cv.modern-professional', ['cv' => $cvData])->render();

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Component Test', $html);
        $this->assertStringNotContainsString('@dd', $html);
    }

    public function test_templates_render_rtl_for_arabic(): void
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'name' => 'Arabic CV',
            'language' => 'ar',
            'info' => ['firstName' => 'أحمد', 'lastName' => 'محمد'],
        ]);

        $cvData = $this->mapper->formatProfileResponse($profile);

        $html = view('templates.cv.modern-professional', ['cv' => $cvData])->render();

        $this->assertStringContainsString('dir="rtl"', $html);
    }
}
