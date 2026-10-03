<?php

namespace Tests\Feature\Api;

use App\Enums\ContactMessageModerationStatus;
use App\Enums\HelpArticleKind;
use App\Models\FeatureRequest;
use App\Models\HelpArticle;
use App\Models\ProductTour;
use App\Models\ProductTourStep;
use App\Models\PublicProfile;
use App\Models\PublicProfileTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_help_articles_list_and_show(): void
    {
        HelpArticle::create([
            'kind' => HelpArticleKind::Faq,
            'category' => 'billing',
            'slug' => 'how-billing-works',
            'title' => ['en' => 'How does billing work?'],
            'body' => ['en' => 'Billing is monthly and you can cancel anytime.'],
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->getJson('/api/v1/support/help-articles?kind=faq')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('result.0.slug', 'how-billing-works');

        $this->getJson('/api/v1/support/help-articles/how-billing-works')
            ->assertOk()
            ->assertJsonPath('result.body', 'Billing is monthly and you can cancel anytime.');
    }

    public function test_portal_help_articles_requires_auth(): void
    {
        $this->getJson('/api/v1/portal/help-articles')->assertUnauthorized();
    }

    public function test_feature_request_flow(): void
    {
        $author = User::factory()->create(['active' => true]);
        $voter = User::factory()->create(['active' => true]);

        $published = FeatureRequest::create([
            'user_id' => $author->id,
            'title' => 'Dark mode',
            'body' => 'Please add dark mode to the portal.',
            'is_published' => true,
            'vote_count' => 0,
        ]);

        FeatureRequest::create([
            'user_id' => $author->id,
            'title' => 'Secret idea',
            'body' => 'Not published yet.',
            'is_published' => false,
            'vote_count' => 0,
        ]);

        $this->getJson('/api/v1/support/feature-requests')
            ->assertOk()
            ->assertJsonCount(1, 'result');

        Sanctum::actingAs($voter);
        $this->postJson('/api/v1/support/feature-requests/'.$published->id.'/vote')
            ->assertOk()
            ->assertJsonPath('result.vote_count', 1);

        $this->postJson('/api/v1/support/feature-requests/'.$published->id.'/vote')
            ->assertStatus(409);
    }

    public function test_feature_request_create_not_published(): void
    {
        $user = User::factory()->create(['active' => true]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/support/feature-requests', [
            'title' => 'Export to Word',
            'body' => 'It would be great to export CVs to DOCX format.',
        ])->assertCreated();

        $this->assertDatabaseHas('feature_requests', [
            'user_id' => $user->id,
            'is_published' => false,
        ]);
    }

    public function test_support_ticket_submit(): void
    {
        $this->postJson('/api/v1/support/tickets', [
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'subject' => 'Cannot log in',
            'body' => 'I reset my password but still cannot access my account.',
        ])->assertCreated()
            ->assertJsonPath('result.status', 'open');
    }

    public function test_product_tour_offer_and_progress(): void
    {
        $user = User::factory()->create(['active' => true]);
        Sanctum::actingAs($user);

        $tour = ProductTour::create(['key' => 'portal_intro', 'is_enabled' => true, 'sort_order' => 1]);
        ProductTourStep::create([
            'product_tour_id' => $tour->id,
            'sort_order' => 1,
            'title' => ['en' => 'Welcome'],
            'body' => ['en' => 'This is your dashboard.'],
            'is_enabled' => true,
        ]);

        $this->getJson('/api/v1/portal/tours/offer')
            ->assertOk()
            ->assertJsonPath('result.tours.0.key', 'portal_intro')
            ->assertJsonCount(1, 'result.tours.0.steps');

        $this->postJson('/api/v1/portal/tours/portal_intro/complete')->assertOk();

        $this->getJson('/api/v1/portal/tours/offer')
            ->assertOk()
            ->assertJsonPath('result.tours', []);

        $this->postJson('/api/v1/portal/tours/portal_intro/reset')
            ->assertOk()
            ->assertJsonPath('result.reset', true);

        $this->getJson('/api/v1/portal/tours/offer')
            ->assertOk()
            ->assertJsonPath('result.tours.0.key', 'portal_intro');
    }

    public function test_distinct_senders_are_delivered_during_profile_traffic(): void
    {
        Mail::fake();
        Notification::fake();

        config([
            'contact.moderation.max_links_before_review' => 10,
            'contact.moderation.profile_burst_threshold' => 5,
            'contact.moderation.profile_burst_window_minutes' => 15,
        ]);

        $user = User::factory()->create(['active' => true, 'notify_contact_email' => true]);
        $profile = $this->makeProfile($user);

        for ($i = 1; $i <= 5; $i++) {
            $this->from('/u/'.$profile->slug)
                ->post('/u/'.$profile->slug.'/contact', [
                    'name' => 'Visitor '.$i,
                    'email' => "visitor{$i}@example.com",
                    'message' => 'Hello, I saw your profile and would like to connect about work.',
                ])
                ->assertRedirect();
        }

        $this->assertDatabaseCount('contact_messages', 5);
        $this->assertEquals(
            5,
            \App\Models\ContactMessage::query()
                ->where('moderation_status', ContactMessageModerationStatus::Approved->value)
                ->count()
        );
        Notification::assertCount(5);
    }

    public function test_repeated_sender_hits_rate_limit(): void
    {
        Mail::fake();
        Notification::fake();

        config([
            'contact.moderation.max_links_before_review' => 10,
            'contact.sender_rate_limit.max_attempts' => 3,
            'contact.sender_rate_limit.decay_seconds' => 300,
        ]);

        $user = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($user);

        $payload = [
            'name' => 'Repeat',
            'email' => 'repeat@example.com',
            'message' => 'Hello again, still interested in talking about opportunities.',
        ];

        for ($i = 0; $i < 3; $i++) {
            $this->from('/u/'.$profile->slug)
                ->post('/u/'.$profile->slug.'/contact', $payload)
                ->assertRedirect();
        }

        $this->from('/u/'.$profile->slug)
            ->post('/u/'.$profile->slug.'/contact', $payload)
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('contact_messages', 3);
    }

    public function test_contact_message_with_many_links_held_for_review(): void
    {
        Mail::fake();
        Notification::fake();

        config([
            'contact.moderation.max_links_before_review' => 2,
            'contact.moderation.profile_burst_threshold' => 100,
        ]);

        $user = User::factory()->create(['active' => true, 'notify_contact_email' => true]);
        $profile = $this->makeProfile($user);

        $this->from('/u/'.$profile->slug)
            ->post('/u/'.$profile->slug.'/contact', [
                'name' => 'Recruiter',
                'email' => 'links@example.com',
                'message' => 'See https://a.test https://b.test https://c.test for details.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'links@example.com',
            'moderation_status' => ContactMessageModerationStatus::PendingReview->value,
        ]);

        Notification::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_pending_contact_hidden_from_owner_inbox(): void
    {
        $owner = User::factory()->create(['active' => true]);
        $profile = $this->makeProfile($owner);

        \App\Models\ContactMessage::create([
            'public_profile_id' => $profile->id,
            'user_id' => $owner->id,
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'message' => 'Pending moderation message content here.',
            'moderation_status' => ContactMessageModerationStatus::PendingReview,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/public-profiles/inbox')
            ->assertOk()
            ->assertJsonPath('result', []);
    }

    private function makeProfile(User $user): PublicProfile
    {
        $template = PublicProfileTemplate::create([
            'name' => 'minimal-folio',
            'preview' => 'x.png',
            'is_active' => true,
            'is_default' => true,
        ]);

        return PublicProfile::create([
            'user_id' => $user->id,
            'public_profile_template_id' => $template->id,
            'slug' => 'support-test',
            'is_public' => true,
            'enable_contact_form' => true,
            'enable_subdomain' => false,
            'headline' => 'Tester',
            'info' => ['firstName' => 'Test', 'lastName' => 'User', 'email' => $user->email],
        ]);
    }
}
