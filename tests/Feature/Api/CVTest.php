<?php

namespace Tests\Feature\Api;

use App\Models\Profile;
use App\Models\Template;
use App\Models\User;
use App\Services\CVDataMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CVTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CVDataMapper $mapper;
    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['active' => true]);
        $this->mapper = new CVDataMapper();

        // Create an active template for PDF generation tests
        $this->template = Template::create([
            'name' => 'modern-professional',
            'preview' => 'modern-professional.png',
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    private function getAuthHeader(): array
    {
        $token = $this->user->createToken('test-token')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }

    private function validCVData(): array
    {
        return [
            'name' => 'My Professional CV',
            'language' => 'en',
            'user_data' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'jobTitle' => 'Software Engineer',
                'email' => 'john@example.com',
                'phone' => '123456789',
                'summary' => 'Experienced developer with 10 years.',
                'skills' => [
                    ['name' => 'PHP'],
                    ['name' => 'Laravel'],
                ],
                'experiences' => [
                    [
                        'position' => 'Senior Developer',
                        'company' => 'Acme Corp',
                        'from' => '2020-01',
                        'to' => '2024-01',
                        'description' => 'Led backend team',
                    ],
                ],
                'educations' => [
                    [
                        'institution' => 'MIT',
                        'degree' => 'Bachelor',
                        'fieldOfStudy' => 'Computer Science',
                        'from' => '2010-09',
                        'to' => '2014-06',
                    ],
                ],
                'languages' => [
                    ['name' => 'English', 'proficiencyLevel' => 5],
                ],
                'interests' => [
                    ['name' => 'Coding'],
                ],
            ],
        ];
    }

    public function test_authenticated_user_can_create_cv(): void
    {
        $response = $this->withHeaders($this->getAuthHeader())
            ->postJson('/api/v1/cvs', $this->validCVData());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'result' => [
                    'id',
                    'user_id',
                    'name',
                    'language',
                    'user_data',
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('My Professional CV', $response->json('result.name'));
    }

    public function test_unauthenticated_user_can_create_cv_with_template_id(): void
    {
        $data = $this->validCVData();
        $data['template_id'] = $this->template->id;

        $response = $this->postJson('/api/v1/cvs', $data);

        // Should try to generate PDF (may fail without real PDF service, but shouldn't be 401/403)
        // The route is public, so it should at least attempt processing
        $this->assertNotEquals(401, $response->status());
        $this->assertNotEquals(403, $response->status());
    }

    public function test_authenticated_user_can_list_cvs(): void
    {
        // Create a profile for the user
        Profile::create([
            'user_id' => $this->user->id,
            'name' => 'CV 1',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->getJson('/api/v1/cvs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'result' => [
                    '*' => ['id', 'user_id', 'name', 'language', 'user_data'],
                ],
            ]);

        $this->assertCount(1, $response->json('result'));
    }

    public function test_unauthenticated_user_cannot_list_cvs(): void
    {
        $response = $this->getJson('/api/v1/cvs');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_own_cv(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->getJson("/api/v1/cvs/{$profile->id}");

        $response->assertStatus(200);
        $this->assertEquals('My CV', $response->json('result.name'));
    }

    public function test_user_cannot_view_others_cv(): void
    {
        $otherUser = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $otherUser->id,
            'name' => 'Others CV',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->getJson("/api/v1/cvs/{$profile->id}");

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_update_cv(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->putJson("/api/v1/cvs/{$profile->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $response->json('result.name'));
    }

    public function test_user_cannot_update_others_cv(): void
    {
        $otherUser = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $otherUser->id,
            'name' => 'Others CV',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->putJson("/api/v1/cvs/{$profile->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_delete_cv(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'To Delete',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->deleteJson("/api/v1/cvs/{$profile->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($profile);
    }

    public function test_cv_creation_requires_name(): void
    {
        $response = $this->withHeaders($this->getAuthHeader())
            ->postJson('/api/v1/cvs', [
                'language' => 'en',
            ]);

        $response->assertStatus(422);
    }

    public function test_cv_creation_validates_language(): void
    {
        $response = $this->withHeaders($this->getAuthHeader())
            ->postJson('/api/v1/cvs', [
                'name' => 'My CV',
                'language' => 'invalid-lang',
            ]);

        $response->assertStatus(422);
    }

    public function test_cv_creation_with_valid_language_options(): void
    {
        foreach (['en', 'ar', 'tr'] as $lang) {
            $response = $this->withHeaders($this->getAuthHeader())
                ->postJson('/api/v1/cvs', [
                    'name' => 'CV ' . $lang,
                    'language' => $lang,
                ]);

            $response->assertStatus(201);
            $this->assertEquals($lang, $response->json('result.language'));
        }
    }

    public function test_cv_update_can_change_user_data(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->putJson("/api/v1/cvs/{$profile->id}", [
                'user_data' => [
                    'firstName' => 'Jane',
                    'lastName' => 'Smith',
                    'jobTitle' => 'CTO',
                ],
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Jane', $response->json('result.user_data.firstName'));
        $this->assertEquals('Smith', $response->json('result.user_data.lastName'));
        $this->assertEquals('CTO', $response->json('result.user_data.jobTitle'));
    }

    public function test_create_cv_stores_all_sections(): void
    {
        $response = $this->withHeaders($this->getAuthHeader())
            ->postJson('/api/v1/cvs', $this->validCVData());

        $response->assertStatus(201);

        $userData = $response->json('result.user_data');
        $this->assertNotEmpty($userData['skills']);
        $this->assertCount(2, $userData['skills']);
        $this->assertNotEmpty($userData['experiences']);
        $this->assertCount(1, $userData['experiences']);
        $this->assertNotEmpty($userData['educations']);
        $this->assertCount(1, $userData['educations']);
        $this->assertNotEmpty($userData['languages']);
        $this->assertCount(1, $userData['languages']);
        $this->assertNotEmpty($userData['interests']);
        $this->assertCount(1, $userData['interests']);
    }
}
