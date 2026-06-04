<?php

namespace Tests\Unit\Models;

use App\Models\CoverLetterTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverLetterTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_can_be_created(): void
    {
        $template = CoverLetterTemplate::create([
            'name' => 'Professional',
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('cover_letter_templates', ['name' => 'Professional']);
        $this->assertTrue($template->is_active);
    }

    public function test_template_default_toggle(): void
    {
        $template = CoverLetterTemplate::create([
            'name' => 'Default Template',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->assertTrue($template->is_default);
    }
}
