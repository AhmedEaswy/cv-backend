<?php

namespace Tests\Feature\Api;

use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverLetterApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CoverLetterTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['active' => true]);

        $this->template = CoverLetterTemplate::create([
            'name' => 'Professional',
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    private function authHeader(): array
    {
        $token = $this->user->createToken('test')->plainTextToken;

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_authenticated_user_can_create_cover_letter(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/cover-letters', [
                'name' => 'My Cover Letter',
                'language' => 'en',
                'user_data' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'john@example.com',
                ],
            ]);

        $response->assertStatus(201);
        $this->assertEquals('My Cover Letter', $response->json('result.name'));
    }

    public function test_user_can_list_their_cover_letters(): void
    {
        CoverLetter::create([
            'user_id' => $this->user->id,
            'name' => 'CL 1',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/cover-letters');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('result'));
    }

    public function test_unauthenticated_cannot_list_cover_letters(): void
    {
        $response = $this->getJson('/api/v1/cover-letters');
        $response->assertStatus(401);
    }

    public function test_user_can_view_own_cover_letter(): void
    {
        $cl = CoverLetter::create([
            'user_id' => $this->user->id,
            'name' => 'My CL',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/v1/cover-letters/{$cl->id}");

        $response->assertStatus(200);
        $this->assertEquals('My CL', $response->json('result.name'));
    }

    public function test_user_can_update_cover_letter(): void
    {
        $cl = CoverLetter::create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/v1/cover-letters/{$cl->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $response->json('result.name'));
    }

    public function test_user_can_delete_cover_letter(): void
    {
        $cl = CoverLetter::create([
            'user_id' => $this->user->id,
            'name' => 'To Delete',
            'language' => 'en',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/v1/cover-letters/{$cl->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($cl);
    }

    public function test_user_can_get_active_templates(): void
    {
        $response = $this->getJson('/api/v1/cover-letters/templates');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('result'));
    }

    public function test_creation_validates_required_name(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/cover-letters', [
                'language' => 'en',
            ]);

        $response->assertStatus(422);
    }
}
