<?php

namespace Tests\Unit\Models;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_profile_can_be_created(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
            'info' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@example.com',
            ],
        ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
        ]);
    }

    public function test_profile_belongs_to_user(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
        ]);

        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertEquals($this->user->id, $profile->user->id);
    }

    public function test_profile_casts_are_correct(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
            'is_public' => true,
            'sections_order' => ['summary', 'experience'],
            'interests' => [['interest' => 'Coding']],
            'languages' => [['language' => 'English', 'level' => 'native']],
            'info' => ['firstName' => 'John'],
            'experiences' => [
                ['position' => 'Developer', 'name' => 'Acme', 'currentlyWorkingHere' => true],
            ],
            'projects' => [
                ['name' => 'Project X', 'description' => 'Cool project'],
            ],
            'educations' => [
                ['institution' => 'MIT', 'degree' => 'BS'],
            ],
        ]);

        $this->assertIsBool($profile->is_public);
        $this->assertTrue($profile->is_public);
        $this->assertIsArray($profile->sections_order);
        $this->assertIsArray($profile->info);
        $this->assertIsArray($profile->experiences);
        $this->assertIsArray($profile->educations);
        $this->assertIsArray($profile->projects);
        $this->assertIsArray($profile->languages);
        $this->assertIsArray($profile->interests);
    }

    public function test_profile_can_be_soft_deleted(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
        ]);

        $profile->delete();

        $this->assertSoftDeleted($profile);
        $this->assertDatabaseMissing('profiles', ['id' => $profile->id, 'deleted_at' => null]);
    }

    public function test_profile_can_be_restored_after_soft_delete(): void
    {
        $profile = Profile::create([
            'user_id' => $this->user->id,
            'name' => 'My CV',
            'language' => 'en',
        ]);

        $profile->delete();
        $profile->restore();

        $this->assertDatabaseHas('profiles', ['id' => $profile->id, 'deleted_at' => null]);
    }
}
