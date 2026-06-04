<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePreviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['active' => true]);

        // Create active template whose name maps to a view
        $this->template = Template::create([
            'name' => 'modern-professional',
            'preview' => 'modern.png',
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    public function test_profile_preview_returns_200_for_valid_profile(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'Test CV',
            'language' => 'en',
            'template_id' => $this->template->id,
            'info' => ['firstName' => 'John', 'lastName' => 'Doe'],
        ]);

        $response = $this->get("/profile/{$profile->id}");

        $response->assertStatus(200);
    }

    public function test_profile_preview_returns_404_for_non_existent_profile(): void
    {
        $response = $this->get('/profile/99999');

        $response->assertStatus(404);
    }

    public function test_profile_preview_uses_default_template_when_no_template_id(): void
    {
        // Create default template
        $defaultTemplate = Template::create([
            'name' => 'modern-professional',
            'preview' => 'default.png',
            'is_active' => true,
            'is_default' => true,
        ]);

        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'Default CV',
            'language' => 'en',
            'info' => ['firstName' => 'Jane', 'lastName' => 'Smith'],
        ]);

        $response = $this->get("/profile/{$profile->id}");

        $response->assertStatus(200);
    }

    public function test_profile_preview_returns_404_for_inactive_template(): void
    {
        $inactiveTemplate = Template::create([
            'name' => 'old-template',
            'preview' => 'old.png',
            'is_active' => false,
            'is_default' => false,
        ]);

        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'Inactive Template CV',
            'language' => 'en',
            'template_id' => $inactiveTemplate->id,
        ]);

        $response = $this->get("/profile/{$profile->id}");

        $response->assertStatus(404);
    }

    public function test_profile_preview_can_override_template_via_query_param(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'Override CV',
            'language' => 'en',
            'info' => ['firstName' => 'Override', 'lastName' => 'Test'],
        ]);

        $response = $this->get("/profile/{$profile->id}?template_id={$this->template->id}");

        $response->assertStatus(200);
    }
}
