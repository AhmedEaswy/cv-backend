<?php

namespace Tests\Unit\Models;

use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverLetterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cover_letter_can_be_created(): void
    {
        $user = User::factory()->create();

        $coverLetter = CoverLetter::create([
            'user_id' => $user->id,
            'name' => 'My Cover Letter',
            'language' => 'en',
        ]);

        $this->assertDatabaseHas('cover_letters', ['name' => 'My Cover Letter']);
        $this->assertEquals($user->id, $coverLetter->user_id);
    }

    public function test_cover_letter_has_user_relation(): void
    {
        $user = User::factory()->create();
        $coverLetter = CoverLetter::create([
            'user_id' => $user->id,
            'name' => 'Test CL',
            'language' => 'en',
        ]);

        $this->assertEquals($user->id, $coverLetter->user->id);
    }

    public function test_cover_letter_has_template_relation(): void
    {
        $user = User::factory()->create();
        $template = CoverLetterTemplate::create(['name' => 'Default', 'is_active' => true]);
        $coverLetter = CoverLetter::create([
            'user_id' => $user->id,
            'name' => 'Test CL',
            'language' => 'en',
            'cover_letter_template_id' => $template->id,
        ]);

        $this->assertEquals($template->id, $coverLetter->template->id);
    }

    public function test_cover_letter_tracking_fields(): void
    {
        $user = User::factory()->create();
        $coverLetter = CoverLetter::create([
            'user_id' => $user->id,
            'name' => 'Tracked CL',
            'language' => 'en',
            'ip_address' => '192.168.1.1',
            'country' => 'Egypt',
            'device' => 'Desktop / Windows / Chrome',
        ]);

        $this->assertEquals('Egypt', $coverLetter->country);
        $this->assertEquals('192.168.1.1', $coverLetter->ip_address);
    }
}
