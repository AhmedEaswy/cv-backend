<?php

namespace Tests\Unit\Support;

use App\Support\CvLink;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CvLinkTest extends TestCase
{
    #[DataProvider('hrefProvider')]
    public function test_href_detection(string $input, ?string $expected): void
    {
        $this->assertSame($expected, CvLink::href($input));
    }

    public static function hrefProvider(): array
    {
        return [
            'email' => ['jane@example.com', 'mailto:jane@example.com'],
            'phone' => ['+1 (555) 123-4567', 'tel:+15551234567'],
            'https' => ['https://example.com/cv', 'https://example.com/cv'],
            'www' => ['www.example.com', 'https://www.example.com'],
            'bare domain' => ['linkedin.com/in/jane', 'https://linkedin.com/in/jane'],
            'address stays plain' => ['123 Main Street, City', null],
            'empty' => ['  ', null],
        ];
    }

    public function test_tag_renders_anchor_for_email(): void
    {
        $html = CvLink::tag('jane@example.com');
        $this->assertStringContainsString('href="mailto:jane@example.com"', $html);
        $this->assertStringContainsString('jane@example.com', $html);
    }

    public function test_tag_escapes_plain_text(): void
    {
        $html = CvLink::tag('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
