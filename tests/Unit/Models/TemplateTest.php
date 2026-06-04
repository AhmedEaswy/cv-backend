<?php

namespace Tests\Unit\Models;

use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_can_be_created(): void
    {
        $template = Template::create([
            'name' => 'Modern Professional',
            'preview' => 'previews/modern.png',
            'description' => 'A modern professional CV template',
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('templates', [
            'id' => $template->id,
            'name' => 'Modern Professional',
            'is_active' => true,
            'is_default' => false,
        ]);
    }

    public function test_template_casts_are_correct(): void
    {
        $template = Template::create([
            'name' => 'Modern Professional',
            'preview' => 'preview.png',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->assertIsBool($template->is_active);
        $this->assertTrue($template->is_active);
        $this->assertIsBool($template->is_default);
        $this->assertTrue($template->is_default);
    }

    public function test_template_can_be_soft_deleted(): void
    {
        $template = Template::create([
            'name' => 'Old Template',
            'preview' => 'old.png',
            'is_active' => false,
        ]);

        $template->delete();

        $this->assertSoftDeleted($template);
    }

    public function test_template_fillable_attributes(): void
    {
        $template = Template::create([
            'name' => 'Corporate',
            'preview' => 'previews/corp.png',
            'description' => 'A corporate style CV',
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->assertEquals('Corporate', $template->name);
        $this->assertEquals('previews/corp.png', $template->preview);
        $this->assertEquals('A corporate style CV', $template->description);
    }
}
