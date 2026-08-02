<?php

namespace Tests\Unit\Services\Ats;

use App\Services\Ats\AtsKeywordMatcher;
use Tests\TestCase;

class AtsKeywordMatcherTest extends TestCase
{
    public function test_extracts_and_matches_keywords(): void
    {
        $matcher = new AtsKeywordMatcher;

        $jd = 'Looking for a Flutter developer with Dart, Firebase, and CI/CD experience. Strong teamwork required.';
        $cv = 'Senior Flutter engineer using Dart and Firebase. Built mobile apps.';

        $result = $matcher->match($jd, $cv);

        $this->assertContains('flutter', $result['matched']);
        $this->assertContains('dart', $result['matched']);
        $this->assertContains('firebase', $result['matched']);
        $this->assertNotEmpty($result['missing']);
        $this->assertGreaterThan(0, $result['coverage_percent']);
        $this->assertLessThanOrEqual(100, $result['coverage_percent']);
    }

    public function test_empty_job_description_terms_yield_full_coverage(): void
    {
        $matcher = new AtsKeywordMatcher;

        $result = $matcher->match('the and or', 'anything');

        $this->assertSame([], $result['matched']);
        $this->assertSame([], $result['missing']);
        $this->assertSame(100, $result['coverage_percent']);
    }
}
