<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_touch_sets_last_used_and_accumulates_platforms(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'last_used_at' => null,
            'used_platforms' => null,
            'uses_both_platforms' => false,
        ]);

        $service = new UserActivityService;
        $service->touch($user->fresh(), 'web');

        $user->refresh();
        $this->assertNotNull($user->last_used_at);
        $this->assertSame('web', $user->last_app_platform);
        $this->assertSame(['web'], $user->used_platforms);
        $this->assertFalse($user->uses_both_platforms);

        $service->touch($user->fresh(), 'ios');
        $user->refresh();

        $this->assertEqualsCanonicalizing(['web', 'ios'], $user->used_platforms);
        $this->assertTrue($user->uses_both_platforms);
        $this->assertSame('ios', $user->last_app_platform);
    }

    public function test_detect_uses_both_requires_web_and_mobile(): void
    {
        $service = new UserActivityService;

        $this->assertFalse($service->detectUsesBoth(['web']));
        $this->assertFalse($service->detectUsesBoth(['ios', 'android']));
        $this->assertTrue($service->detectUsesBoth(['web', 'android']));
        $this->assertTrue($service->detectUsesBoth(['ios', 'web']));
    }
}
