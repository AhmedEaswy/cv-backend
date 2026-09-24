<?php

namespace Tests\Feature\Api;

use App\Jobs\RecordAnalyticsEvent;
use App\Models\AnalyticsEvent;
use App\Models\AnonymousUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsTrackingEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_click_records_structured_device_and_anonymous_id(): void
    {
        Queue::fake();

        $anonymousId = (string) Str::uuid();

        $this->withHeaders([
            'X-Anonymous-Id' => $anonymousId,
            'X-App-Platform' => 'web',
            'X-App-Version' => 'web-1',
            'X-Device-Type' => 'Desktop',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->postJson('/api/v1/analytics/click', [
            'target' => 'app_store',
            'page' => 'landing',
        ])->assertNoContent();

        Queue::assertPushed(RecordAnalyticsEvent::class, function (RecordAnalyticsEvent $job) use ($anonymousId) {
            $payload = (new \ReflectionClass($job))->getProperty('payload');
            $payload->setAccessible(true);
            $data = $payload->getValue($job);

            return $data['action_type'] === 'click_app_store'
                && $data['anonymous_user_id'] === strtolower($anonymousId)
                && $data['app_platform'] === 'web'
                && $data['app_version'] === 'web-1'
                && ($data['meta']['page'] ?? null) === 'landing'
                && ($data['locale'] ?? null) === 'en-us';
        });
    }

    public function test_middleware_strips_photo_from_request_data_and_sets_meta(): void
    {
        Queue::fake();

        $anonymousId = (string) Str::uuid();

        // Public create without template goes through store (may 422 on name etc. — use print)
        // Use a minimal valid print path is heavy; hit cover-letters create instead.
        $this->withHeaders([
            'X-Anonymous-Id' => $anonymousId,
            'X-App-Platform' => 'ios',
            'X-App-Version' => '1.3.0+16',
            'X-OS-Version' => '17.2',
            'X-Device-Model' => 'iPhone',
        ])->postJson('/api/v1/cover-letters', [
            'name' => 'Letter',
            'language' => 'en',
            'client_ref' => 'local-cl-1',
            'template_id' => null,
            'user_data' => [
                'firstName' => 'Ada',
                'lastName' => 'Lovelace',
                'photo' => 'data:image/png;base64,'.str_repeat('A', 100),
            ],
        ]);

        Queue::assertPushed(RecordAnalyticsEvent::class, function (RecordAnalyticsEvent $job) {
            $payload = (new \ReflectionClass($job))->getProperty('payload');
            $payload->setAccessible(true);
            $data = $payload->getValue($job);

            $hasNoPhoto = ! isset($data['request_data']['user_data']['photo']);
            $metaHasClientRef = ($data['meta']['client_ref'] ?? null) === 'local-cl-1';
            $platformOk = ($data['app_platform'] ?? null) === 'ios';
            $modelOk = ($data['device_model'] ?? null) === 'iPhone';

            return $data['action_type'] === 'create_cover_letter'
                && $hasNoPhoto
                && $metaHasClientRef
                && $platformOk
                && $modelOk;
        });
    }

    public function test_anonymous_user_stores_structured_last_seen_fields(): void
    {
        $anonymousId = (string) Str::uuid();

        $this->withHeaders([
            'X-Anonymous-Id' => $anonymousId,
            'X-App-Platform' => 'android',
            'X-App-Version' => '1.2.0',
            'X-OS-Version' => '14',
            'X-Device-Model' => 'Pixel 8',
        ])->postJson('/api/v1/cover-letters', [
            'name' => 'Letter',
            'language' => 'en',
            'client_ref' => 'cl-1',
            'user_data' => [
                'firstName' => 'Ada',
                'lastName' => 'Lovelace',
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('anonymous_users', [
            'id' => strtolower($anonymousId),
            'app_platform' => 'android',
            'app_version' => '1.2.0',
            'os_version' => '14',
            'device_model' => 'Pixel 8',
        ]);

        $this->assertInstanceOf(AnonymousUser::class, AnonymousUser::find(strtolower($anonymousId)));
    }

    public function test_report_overview_counts_seeded_events(): void
    {
        AnalyticsEvent::create([
            'endpoint' => 'api/v1/cvs/print',
            'method' => 'POST',
            'action_type' => 'print_cv',
            'app_platform' => 'ios',
            'created_at' => now(),
        ]);
        AnalyticsEvent::create([
            'endpoint' => 'api/v1/analytics/click',
            'method' => 'POST',
            'action_type' => 'click_app_store',
            'app_platform' => 'web',
            'created_at' => now(),
        ]);

        $service = app(\App\Services\Reports\ReportQueryService::class);
        $overview = $service->overview(now()->subDay()->toDateString(), now()->toDateString());

        $this->assertSame(1, $overview['prints']);
        $this->assertSame(1, $overview['store_clicks']);
    }
}
