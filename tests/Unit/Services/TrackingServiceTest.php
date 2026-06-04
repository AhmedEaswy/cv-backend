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
        $this->service = new TrackingService();
    }

    public function test_capture_returns_ip_address(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $result = $this->service->capture($request);

        $this->assertArrayHasKey('ip_address', $result);
        $this->assertEquals('127.0.0.1', $result['ip_address']);
    }

    public function test_capture_returns_device_from_user_agent(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        $result = $this->service->capture($request);

        $this->assertArrayHasKey('device', $result);
        $this->assertStringContainsString('Desktop', $result['device']);
        $this->assertStringContainsString('Windows', $result['device']);
        $this->assertStringContainsString('Chrome', $result['device']);
    }

    public function test_capture_returns_device_for_mobile(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        ]);

        $result = $this->service->capture($request);

        $this->assertStringContainsString('Mobile', $result['device']);
        $this->assertStringContainsString('iOS', $result['device']);
    }

    public function test_capture_returns_device_for_android(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
        ]);

        $result = $this->service->capture($request);

        $this->assertStringContainsString('Android', $result['device']);
    }

    public function test_capture_returns_null_device_for_no_user_agent(): void
    {
        $request = Request::create('/api/v1/cvs', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => '',
        ]);

        $result = $this->service->capture($request);

        $this->assertNull($result['device']);
    }
}
