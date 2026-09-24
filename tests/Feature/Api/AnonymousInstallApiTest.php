<?php

namespace Tests\Feature\Api;

use App\Models\AnonymousUser;
use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Models\Profile;
use App\Models\Template;
use App\Services\CoverLetterDataMapper;
use App\Services\CoverLetterPDFService;
use App\Services\CVDataMapper;
use App\Services\CVPDFService;
use App\Services\CvPhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class AnonymousInstallApiTest extends TestCase
{
    use RefreshDatabase;

    private Template $template;

    private CoverLetterTemplate $coverLetterTemplate;

    private string $anonymousId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->anonymousId = (string) Str::uuid();

        $this->template = Template::create([
            'name' => 'modern-professional',
            'preview' => 'modern-professional.png',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->coverLetterTemplate = CoverLetterTemplate::create([
            'name' => 'Professional',
            'is_active' => true,
            'is_default' => true,
        ]);

        $cvPdf = Mockery::mock(CVPDFService::class, [
            app(CVDataMapper::class),
            app(CvPhotoService::class),
        ])->makePartial();
        $cvPdf->shouldReceive('generatePdf')->andReturn('https://example.com/cv.pdf');
        $this->app->instance(CVPDFService::class, $cvPdf);

        $clPdf = Mockery::mock(CoverLetterPDFService::class, [
            app(CoverLetterDataMapper::class),
        ])->makePartial();
        $clPdf->shouldReceive('generatePdf')->andReturn('https://example.com/cover.pdf');
        $this->app->instance(CoverLetterPDFService::class, $clPdf);
    }

    private function anonymousHeaders(): array
    {
        return ['X-Anonymous-Id' => $this->anonymousId];
    }

    private function cvUserData(string $firstName = 'John'): array
    {
        return [
            'firstName' => $firstName,
            'lastName' => 'Doe',
            'jobTitle' => 'Engineer',
            'email' => 'john@example.com',
            'phone' => '123',
            'summary' => 'Summary',
        ];
    }

    public function test_guest_print_creates_one_profile_for_install(): void
    {
        $response = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-1',
                'name' => 'My CV',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ]);

        $response->assertOk()
            ->assertJsonPath('result.url', 'https://example.com/cv.pdf')
            ->assertJsonStructure(['result' => ['url', 'profile_id']]);

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseCount('anonymous_users', 1);
        $this->assertDatabaseHas('profiles', [
            'id' => $response->json('result.profile_id'),
            'anonymous_user_id' => $this->anonymousId,
            'client_ref' => 'local-cv-1',
            'user_id' => null,
        ]);
    }

    public function test_guest_print_with_same_client_ref_updates_existing_profile(): void
    {
        $first = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-1',
                'name' => 'My CV',
                'language' => 'en',
                'user_data' => $this->cvUserData('John'),
            ]);

        $profileId = $first->json('result.profile_id');

        $second = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-1',
                'profile_id' => $profileId,
                'name' => 'My CV Updated',
                'language' => 'en',
                'user_data' => $this->cvUserData('Jane'),
            ]);

        $second->assertOk();
        $this->assertEquals($profileId, $second->json('result.profile_id'));
        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseHas('profiles', [
            'id' => $profileId,
            'name' => 'My CV Updated',
        ]);

        $profile = Profile::find($profileId);
        $this->assertEquals('Jane', $profile->info['firstName'] ?? null);
    }

    public function test_second_client_ref_creates_another_profile_under_same_install(): void
    {
        $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-1',
                'name' => 'CV One',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ])
            ->assertOk();

        $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-2',
                'name' => 'CV Two',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ])
            ->assertOk();

        $this->assertDatabaseCount('profiles', 2);
        $this->assertDatabaseCount('anonymous_users', 1);
        $this->assertEquals(
            2,
            Profile::query()->where('anonymous_user_id', $this->anonymousId)->count()
        );
    }

    public function test_different_anonymous_id_cannot_update_another_install_profile(): void
    {
        $owner = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-1',
                'name' => 'Owner CV',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ]);

        $profileId = $owner->json('result.profile_id');
        $otherInstall = (string) Str::uuid();

        $this->withHeaders(['X-Anonymous-Id' => $otherInstall])
            ->putJson('/api/v1/cvs/'.$profileId, [
                'name' => 'Hijacked',
                'user_data' => $this->cvUserData('Hacker'),
            ])
            ->assertNotFound();

        $this->withHeaders(['X-Anonymous-Id' => $otherInstall])
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'profile_id' => $profileId,
                'client_ref' => 'local-cv-other',
                'name' => 'Hijacked Print',
                'language' => 'en',
                'user_data' => $this->cvUserData('Hacker'),
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('profiles', [
            'id' => $profileId,
            'name' => 'Owner CV',
            'anonymous_user_id' => $this->anonymousId,
        ]);
        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_guest_can_update_own_profile_via_put(): void
    {
        $created = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-1',
                'name' => 'Owner CV',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ]);

        $profileId = $created->json('result.profile_id');

        $this->withHeaders($this->anonymousHeaders())
            ->putJson('/api/v1/cvs/'.$profileId, [
                'name' => 'Updated via PUT',
                'user_data' => $this->cvUserData('Updated'),
            ])
            ->assertOk()
            ->assertJsonPath('result.name', 'Updated via PUT');

        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_guest_cover_letter_create_then_update_keeps_single_row(): void
    {
        $create = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cover-letters', [
                'name' => 'My Letter',
                'language' => 'en',
                'client_ref' => 'local-cl-1',
                'cover_letter_template_id' => $this->coverLetterTemplate->id,
                'user_data' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'john@example.com',
                    'body' => 'Hello',
                ],
            ]);

        $create->assertCreated();
        $coverLetterId = $create->json('result.id');

        $this->assertDatabaseHas('cover_letters', [
            'id' => $coverLetterId,
            'anonymous_user_id' => $this->anonymousId,
            'client_ref' => 'local-cl-1',
            'user_id' => null,
        ]);

        $this->withHeaders($this->anonymousHeaders())
            ->putJson('/api/v1/cover-letters/'.$coverLetterId, [
                'name' => 'My Letter Updated',
                'user_data' => [
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'body' => 'Updated body',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('result.name', 'My Letter Updated');

        $this->assertDatabaseCount('cover_letters', 1);

        // Same client_ref on create upserts instead of inserting.
        $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cover-letters', [
                'name' => 'My Letter Again',
                'language' => 'en',
                'client_ref' => 'local-cl-1',
                'user_data' => [
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'body' => 'Again',
                ],
            ])
            ->assertOk();

        $this->assertDatabaseCount('cover_letters', 1);
        $this->assertDatabaseHas('cover_letters', [
            'id' => $coverLetterId,
            'name' => 'My Letter Again',
        ]);
    }

    public function test_guest_cover_letter_print_reuses_client_ref(): void
    {
        $first = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cover-letters/print', [
                'template_id' => $this->coverLetterTemplate->id,
                'client_ref' => 'local-cl-print',
                'name' => 'Print Letter',
                'language' => 'en',
                'user_data' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'john@example.com',
                    'body' => 'Hello',
                ],
            ]);

        $first->assertOk()
            ->assertJsonStructure(['result' => ['url', 'cover_letter_id']]);

        $id = $first->json('result.cover_letter_id');

        $second = $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cover-letters/print', [
                'template_id' => $this->coverLetterTemplate->id,
                'client_ref' => 'local-cl-print',
                'cover_letter_id' => $id,
                'name' => 'Print Letter 2',
                'language' => 'en',
                'user_data' => [
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'email' => 'jane@example.com',
                    'body' => 'Updated',
                ],
            ]);

        $second->assertOk();
        $this->assertEquals($id, $second->json('result.cover_letter_id'));
        $this->assertDatabaseCount('cover_letters', 1);
        $this->assertDatabaseHas('cover_letters', [
            'id' => $id,
            'name' => 'Print Letter 2',
        ]);
    }

    public function test_invalid_anonymous_id_header_is_ignored(): void
    {
        $this->withHeaders(['X-Anonymous-Id' => 'not-a-uuid'])
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'local-cv-1',
                'name' => 'No Install',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ])
            ->assertOk();

        $this->assertDatabaseCount('anonymous_users', 0);
        $this->assertDatabaseCount('profiles', 1);
        $this->assertNull(Profile::first()->anonymous_user_id);
    }

    public function test_anonymous_user_model_has_profiles_relation(): void
    {
        $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'a',
                'name' => 'A',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ])
            ->assertOk();

        $this->withHeaders($this->anonymousHeaders())
            ->postJson('/api/v1/cvs/print', [
                'template_id' => $this->template->id,
                'client_ref' => 'b',
                'name' => 'B',
                'language' => 'en',
                'user_data' => $this->cvUserData(),
            ])
            ->assertOk();

        $anonymous = AnonymousUser::find($this->anonymousId);
        $this->assertNotNull($anonymous);
        $this->assertCount(2, $anonymous->profiles);

        $first = $anonymous->profiles->first();
        $this->assertCount(1, $first->siblingProfiles);
    }
}
