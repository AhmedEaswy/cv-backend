<?php

namespace Tests\Feature;

use App\Support\TemplatePreviewSample;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TemplatePreviewSampleTest extends TestCase
{
    public function test_sample_data_renders_every_shipped_template(): void
    {
        $kinds = [
            'cv' => [
                'directory' => resource_path('views/templates/cv'),
                'view' => 'templates.cv',
                'data' => ['cv' => TemplatePreviewSample::cv(), 'preview' => false],
            ],
            'cover-letter' => [
                'directory' => resource_path('views/templates/cover-letter'),
                'view' => 'templates.cover-letter',
                'data' => ['coverLetter' => TemplatePreviewSample::coverLetter(), 'preview' => false],
            ],
            'public-profile' => [
                'directory' => resource_path('views/templates/public-profile'),
                'view' => 'templates.public-profile',
                'data' => ['profile' => TemplatePreviewSample::publicProfile(), 'preview' => false],
            ],
        ];

        foreach ($kinds as $kind => $config) {
            foreach (File::files($config['directory']) as $file) {
                $slug = $file->getBasename('.blade.php');

                if (str_starts_with($slug, '_')) {
                    continue;
                }

                $html = view($config['view'].'.'.$slug, $config['data'])->render();

                $this->assertStringContainsString('Elena', $html, "{$kind}/{$slug} did not render the sample name");
            }
        }
    }

    public function test_arabic_sample_data_renders_rtl_on_every_shipped_template(): void
    {
        app()->setLocale('ar');

        $kinds = [
            'cv' => [
                'directory' => resource_path('views/templates/cv'),
                'view' => 'templates.cv',
                'data' => ['cv' => TemplatePreviewSample::cv('ar'), 'preview' => false],
            ],
            'cover-letter' => [
                'directory' => resource_path('views/templates/cover-letter'),
                'view' => 'templates.cover-letter',
                'data' => ['coverLetter' => TemplatePreviewSample::coverLetter('ar'), 'preview' => false],
            ],
            'public-profile' => [
                'directory' => resource_path('views/templates/public-profile'),
                'view' => 'templates.public-profile',
                'data' => ['profile' => TemplatePreviewSample::publicProfile('ar'), 'preview' => false],
            ],
        ];

        foreach ($kinds as $kind => $config) {
            foreach (File::files($config['directory']) as $file) {
                $slug = $file->getBasename('.blade.php');

                if (str_starts_with($slug, '_')) {
                    continue;
                }

                $html = view($config['view'].'.'.$slug, $config['data'])->render();

                $this->assertStringContainsString('أحمد', $html, "{$kind}/{$slug} did not render the Arabic sample name");
                $this->assertStringContainsString('dir="rtl"', $html, "{$kind}/{$slug} should render RTL");
            }
        }
    }
}
