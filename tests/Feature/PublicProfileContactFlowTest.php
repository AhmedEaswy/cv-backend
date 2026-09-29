<?php

namespace Tests\Feature;

use App\Jobs\SendContactPushNotificationJob;
use App\Jobs\SendContactReplyJob;
use App\Mail\ContactMessageReceivedMail;
use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use App\Models\ContactSpamBlocklist;
use App\Models\DevicePushToken;
use App\Models\PublicProfile;
use App\Models\PublicProfileTemplate;
use App\Models\User;
use App\Services\Contact\SocialLinksNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PublicProfileContactFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeProfile(User $user, array $overrides = []): PublicProfile
    {
        $template = PublicProfileTemplate::create([
            'name' => 'minimal-folio',
            'preview' => 'x.png',
            'is_active' => true,
            'is_default' => true,
        ]);

        return PublicProfile::create(array_merge([
            'user_id' => $user->id,
            'public_profile_template_id' => $template->id,
            'slug' => 'sara-test',
            'is_public' => true,
            'enable_contact_form' => true,
            'enable_subdomain' => false,
            'headline' => 'Designer',
            'info' => ['firstName' => 'Sara', 'lastName' => 'Test', 'email' => $user->email],
        ], $overrides));
    }

    public function test_contact_submit_creates_message_notification_and_email(): void
    {
        Mail::fake();
        Queue::fake();
        Notification::fake();

        $user = User::factory()->create([
            'active' => true,
            'notify_contact_email' => true,
            'notify_contact_push' => true,
        ]);
        $profile = $this->makeProfile($user);

        $response = $this->from('/u/'.$profile->slug)
            ->post('/u/'.$profile->slug.'/contact', [
                'name' => 'Recruiter',
                'email' => 'recruiter@example.com',
                'subject' => 'Hello',
                'message' => 'We would like to talk about a role with you.',
                'hp_check' => '',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', [
            'public_profile_id' => $profile->id,
            'email' => 'recruiter@example.com',
        ]);

        Notification::assertSentTo($user, \App\Notifications\ContactMessageReceivedNotification::class);
        Mail::assertQueued(ContactMessageReceivedMail::class);
        Queue::assertPushed(SendContactPushNotificationJob::class);
    }

    public function test_contact_skips_email_when_notify_contact_email_false(): void
    {
        Mail::fake();
        Notification::fake();

        $user = User::factory()->create([
            'active' => true,
            'notify_contact_email' => false,
            'notify_contact_push' => false,
        ]);
        $profile = $this->makeProfile($user);

        $this->from('/u/'.$profile->slug)
            ->post('/u/'.$profile->slug.'/contact', [
                'name' => 'Recruiter',
                'email' => 'recruiter@example.com',
                'message' => 'We would like to talk about a role with you.',
            ])
            ->assertRedirect();

        Mail::assertNothingQueued();
        Notification::assertSentTo($user, \App\Notifications\ContactMessageReceivedNotification::class);
    }

    public function test_blocked_sender_cannot_message_any_profile(): void
    {
        Mail::fake();
        Notification::fake();

        ContactSpamBlocklist::create([
            'email' => 'spam@example.com',
            'ip_address' => null,
            'reason' => 'manual',
        ]);

        $user = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($user);

        $this->from('/u/'.$profile->slug)
            ->post('/u/'.$profile->slug.'/contact', [
                'name' => 'Bot',
                'email' => 'spam@example.com',
                'message' => 'Buy cheap products online today please.',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('contact_messages', 0);
        Notification::assertNothingSent();
    }

    public function test_report_spam_blocks_globally(): void
    {
        $owner = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($owner);
        $message = ContactMessage::create([
            'public_profile_id' => $profile->id,
            'user_id' => $owner->id,
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'message' => 'Spam content here for testing blocks.',
            'ip_address' => '1.2.3.4',
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/public-profiles/inbox/'.$message->id.'/spam')
            ->assertOk();

        $this->assertDatabaseHas('contact_spam_blocklist', [
            'email' => 'spam@example.com',
            'reason' => 'reported',
        ]);

        $other = User::factory()->create(['active' => true]);
        $otherProfile = $this->makeProfile($other, ['slug' => 'other-person']);

        $this->from('/u/'.$otherProfile->slug)
            ->post('/u/'.$otherProfile->slug.'/contact', [
                'name' => 'Spammer',
                'email' => 'spam@example.com',
                'message' => 'Trying again on another profile now.',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_inbox_reply_dispatches_job(): void
    {
        Queue::fake();

        $owner = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($owner);
        $message = ContactMessage::create([
            'public_profile_id' => $profile->id,
            'user_id' => $owner->id,
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'message' => 'Interested in chatting about opportunities.',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/public-profiles/inbox/'.$message->id.'/reply', [
            'body' => 'Thanks for reaching out — happy to chat next week.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_message_replies', [
            'contact_message_id' => $message->id,
            'delivery_status' => 'pending',
        ]);
        Queue::assertPushed(SendContactReplyJob::class);
    }

    public function test_notifications_list_and_mark_read(): void
    {
        $owner = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($owner);
        $message = ContactMessage::create([
            'public_profile_id' => $profile->id,
            'user_id' => $owner->id,
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'message' => 'Hello there from a recruiter again.',
        ]);
        $owner->notify(new \App\Notifications\ContactMessageReceivedNotification($message, $profile));

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('success', true);

        $id = $owner->notifications()->first()->id;

        $this->postJson('/api/v1/notifications/'.$id.'/read')->assertOk();
        $this->assertNotNull($owner->notifications()->first()->read_at);
    }

    public function test_push_token_register_and_delete(): void
    {
        $user = User::factory()->create(['active' => true]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/devices/push-token', [
            'token' => 'fcm-token-abc',
            'platform' => 'android',
        ])->assertCreated();

        $this->assertDatabaseHas('device_push_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm-token-abc',
        ]);

        $this->deleteJson('/api/v1/devices/push-token', [
            'token' => 'fcm-token-abc',
        ])->assertOk();

        $this->assertDatabaseCount('device_push_tokens', 0);
    }

    public function test_social_links_normalize_on_profile_update(): void
    {
        $user = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($user);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/public-profiles', [
            'user_data' => [
                'socialLinks' => [
                    ['platform' => 'linkedin', 'url' => 'https://linkedin.com/in/sara'],
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/sara'],
                ],
            ],
        ])->assertOk();

        $profile->refresh();
        $normalized = app(SocialLinksNormalizer::class)->normalize($profile->social_links);

        $this->assertNotEmpty($normalized);
        $platforms = array_column($normalized, 'platform');
        $this->assertContains('linkedin', $platforms);
        $this->assertContains('instagram', $platforms);
    }

    public function test_subdomain_url_helpers_and_enable_flag(): void
    {
        config(['app.profile_domain' => 'cv.test', 'app.url' => 'https://cv.test']);

        $user = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($user, [
            'slug' => 'sara',
            'enable_subdomain' => true,
        ]);

        $this->assertStringContainsString('sara.cv.test', $profile->preferredPublicUrl());
        $this->assertStringContainsString('/u/sara', $profile->pathUrl());
    }

    public function test_notification_settings_update(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'notify_contact_email' => true,
            'notify_contact_push' => true,
        ]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/settings/notifications', [
            'notify_contact_email' => false,
            'notify_contact_push' => false,
        ])->assertOk();

        $user->refresh();
        $this->assertFalse($user->notify_contact_email);
        $this->assertFalse($user->notify_contact_push);
    }

    public function test_honeypot_does_not_create_message(): void
    {
        Notification::fake();
        Mail::fake();

        $user = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($user);

        $this->from('/u/'.$profile->slug)
            ->post('/u/'.$profile->slug.'/contact', [
                'name' => 'Bot',
                'email' => 'bot@example.com',
                'message' => 'This is a long enough spam honeypot payload.',
                'hp_check' => 'https://spam.example',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertDatabaseHas('contact_spam_blocklist', [
            'email' => 'bot@example.com',
            'reason' => 'honeypot',
        ]);
        Notification::assertNothingSent();
    }

    public function test_outbound_mail_hides_password_and_mark_all_read(): void
    {
        $user = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($user);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/settings/outbound-mail', [
            'domain' => 'example.com',
            'from_email' => 'me@example.com',
            'from_name' => 'Me',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'me@example.com',
            'smtp_password' => 'super-secret-password',
            'is_active' => true,
        ])->assertOk();

        $json = $response->json('result');
        $this->assertArrayNotHasKey('smtp_password', $json ?? []);
        $this->assertTrue((bool) ($json['has_smtp_password'] ?? false));

        $message = ContactMessage::create([
            'public_profile_id' => $profile->id,
            'user_id' => $user->id,
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'message' => 'Please mark all of these as read somehow.',
        ]);
        $user->notify(new \App\Notifications\ContactMessageReceivedNotification($message, $profile));

        $this->postJson('/api/v1/notifications/read-all')->assertOk();
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_subdomain_host_serves_public_profile(): void
    {
        config(['app.profile_domain' => 'cv.test', 'app.url' => 'https://cv.test']);

        $user = User::factory()->create(['active' => true]);
        $this->makeProfile($user, [
            'slug' => 'sara',
            'enable_subdomain' => true,
            'headline' => 'Product designer',
        ]);

        $this->get('https://sara.cv.test/')
            ->assertOk()
            ->assertSee('Product designer', false);
    }

    public function test_seo_fields_appear_in_html(): void
    {
        $user = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($user, [
            'slug' => 'seo-person',
            'seo' => [
                'meta_title' => 'SEO Title Person',
                'meta_description' => 'SEO description for the public profile page.',
                'robots' => 'index,follow',
            ],
        ]);

        $this->get('/u/'.$profile->slug)
            ->assertOk()
            ->assertSee('SEO Title Person', false)
            ->assertSee('SEO description for the public profile page.', false);
    }
}
