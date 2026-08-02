<?php

namespace Tests\Unit\Services;

use App\Services\CvPhotoService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvPhotoServiceTest extends TestCase
{
    private CvPhotoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = app(CvPhotoService::class);
    }

    public function test_stores_data_uri_png_and_returns_path(): void
    {
        // 1x1 PNG
        $png = base64_encode(base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
        $dataUri = 'data:image/png;base64,' . $png;

        $userData = $this->service->processUserDataPhoto(['photo' => $dataUri]);

        $this->assertStringStartsWith('cv-photos/', $userData['photo']);
        $this->assertStringEndsWith('.png', $userData['photo']);
        Storage::disk('public')->assertExists($userData['photo']);
    }

    public function test_leaves_http_url_unchanged(): void
    {
        $userData = $this->service->processUserDataPhoto([
            'photo' => 'https://cdn.example.com/me.png',
        ]);

        $this->assertSame('https://cdn.example.com/me.png', $userData['photo']);
    }

    public function test_validation_rejects_invalid_base64(): void
    {
        $error = $this->service->validationError('not-valid-base64!!!');

        $this->assertNotNull($error);
    }

    public function test_url_for_resolves_storage_path(): void
    {
        $url = $this->service->urlFor('cv-photos/demo.png');

        $this->assertStringContainsString('cv-photos/demo.png', $url);
    }
}
