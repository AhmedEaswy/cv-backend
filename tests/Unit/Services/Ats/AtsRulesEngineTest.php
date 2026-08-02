<?php

namespace Tests\Unit\Services\Ats;

use App\Services\Ats\AtsRulesEngine;
use Tests\TestCase;

class AtsRulesEngineTest extends TestCase
{
    public function test_strong_structured_cv_passes_core_checks(): void
    {
        $engine = new AtsRulesEngine;

        $checks = $engine->evaluate([
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'jobTitle' => 'Backend Engineer',
            'email' => 'jane@example.com',
            'phone' => '+1234567890',
            'address' => 'Berlin',
            'summary' => 'Experienced backend engineer specializing in Laravel and scalable APIs.',
            'photo' => null,
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'Laravel'],
                ['name' => 'MySQL'],
            ],
            'experiences' => [
                [
                    'position' => 'Senior Developer',
                    'company' => 'Acme',
                    'from' => '2020-01',
                    'description' => 'Developed and improved API services used by millions of users worldwide.',
                ],
            ],
            'educations' => [
                [
                    'institution' => 'Uni',
                    'degree' => 'BSc',
                    'fieldOfStudy' => 'CS',
                ],
            ],
        ], 'structured');

        $byId = collect($checks)->keyBy('id');

        $this->assertTrue($byId['has_name']['passed']);
        $this->assertTrue($byId['has_email']['passed']);
        $this->assertTrue($byId['has_skills']['passed']);
        $this->assertTrue($byId['action_verbs']['passed']);
        $this->assertTrue($byId['photo_soft_warning']['passed']);
    }

    public function test_empty_cv_fails_core_checks(): void
    {
        $engine = new AtsRulesEngine;

        $checks = $engine->evaluate([], 'structured');
        $failed = collect($checks)->where('passed', false);

        $this->assertTrue($failed->isNotEmpty());
        $this->assertTrue($failed->contains(fn ($c) => $c['id'] === 'has_email'));
    }
}
