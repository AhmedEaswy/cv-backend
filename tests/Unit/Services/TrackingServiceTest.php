<?php

namespace Tests\Unit\Services;

use App\Services\TrackingService;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrackingServiceTest extends TestCase
{
    private TrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrackingService;
    }

    public function test_parses_iphone_user_agent(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
        ]);

        $tracking = $this->service->capture($request);

        $this->assertSame('Mobile', $tracking['device_type']);
        $this->assertSame('iOS', $tracking['os']);
        $this->assertSame('17.2', $tracking['os_version']);
        $this->assertSame('iPhone', $tracking['device_model']);
        $this->assertSame('Safari', $tracking['browser']);
        $this->assertStringContainsString('Mobile', (string) $tracking['device']);
    }

    public function test_prefers_client_headers_over_user_agent(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            'HTTP_X_APP_PLATFORM' => 'android',
            'HTTP_X_APP_VERSION' => '1.3.0+16',
            'HTTP_X_OS_VERSION' => '14',
            'HTTP_X_DEVICE_MODEL' => 'Pixel 8',
            'HTTP_ACCEPT_LANGUAGE' => 'ar-SA,ar;q=0.9',
        ]);

        $tracking = $this->service->capture($request);

        $this->assertSame('android', $tracking['app_platform']);
        $this->assertSame('1.3.0+16', $tracking['app_version']);
        $this->assertSame('14', $tracking['os_version']);
        $this->assertSame('Pixel 8', $tracking['device_model']);
        $this->assertSame('ar-sa', $tracking['locale']);
    }

    public function test_falls_back_to_declared_platform_for_native_clients(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'HTTP_USER_AGENT' => 'Dart/3.8 (dart:io)',
            'HTTP_X_APP_PLATFORM' => 'ios',
            'HTTP_X_OS_VERSION' => '17.2',
            'HTTP_X_DEVICE_TYPE' => 'Mobile',
            'HTTP_X_DEVICE_MODEL' => 'iPhone15,2',
        ]);

        $tracking = $this->service->capture($request);

        $this->assertSame('iOS', $tracking['os']);
        $this->assertSame('Mobile', $tracking['device_type']);
        $this->assertSame('iPhone15,2', $tracking['device_model']);
    }

    public function test_capture_content_only_returns_legacy_fields(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36',
            'HTTP_X_APP_PLATFORM' => 'android',
        ]);

        $content = $this->service->captureContent($request);

        $this->assertArrayHasKey('ip_address', $content);
        $this->assertArrayHasKey('country', $content);
        $this->assertArrayHasKey('device', $content);
        $this->assertArrayNotHasKey('app_platform', $content);
        $this->assertArrayNotHasKey('os_version', $content);
    }
}
