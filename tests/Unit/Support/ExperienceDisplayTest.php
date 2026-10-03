<?php

namespace Tests\Unit\Support;

use App\Support\ExperienceDisplay;
use Tests\TestCase;

class ExperienceDisplayTest extends TestCase
{
    public function test_meta_line_includes_company_location_and_types(): void
    {
        $line = ExperienceDisplay::metaLine([
            'company' => 'Acme',
            'location' => 'Berlin',
            'employmentType' => 'full_time',
            'locationType' => 'remote',
        ]);

        $this->assertSame('Acme · Berlin · '.__('portal.cvs.employment_type.full_time').' · '.__('portal.cvs.location_type.remote'), $line);
    }

    public function test_meta_line_skips_empty_values(): void
    {
        $this->assertSame('Acme', ExperienceDisplay::metaLine([
            'company' => 'Acme',
            'location' => '',
            'employmentType' => null,
            'locationType' => '',
        ]));
    }
}
