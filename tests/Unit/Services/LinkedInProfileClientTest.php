<?php

namespace Tests\Unit\Services;

use App\Services\LinkedIn\LinkedInProfileClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LinkedInProfileClientTest extends TestCase
{
    private LinkedInProfileClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Http::preventStrayRequests();
        $this->client = app(LinkedInProfileClient::class);
    }

    public function test_maps_openid_userinfo_into_cv_user_data(): void
    {
        Http::fake([
            'https://api.linkedin.com/v2/userinfo' => Http::response([
                'sub' => 'li-1',
                'name' => 'Ada Lovelace',
                'given_name' => 'Ada',
                'family_name' => 'Lovelace',
                'email' => 'ada@example.com',
                'picture' => 'https://media.licdn.test/ada.png',
                'locale' => 'en_US',
            ]),
            'https://api.linkedin.com/v2/me*' => Http::response([
                'localizedFirstName' => 'Ada',
                'localizedLastName' => 'Lovelace',
                'localizedHeadline' => 'Software Engineer',
                'vanityName' => 'ada-lovelace',
            ]),
            'https://media.licdn.test/ada.png' => Http::response(
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
                200,
                ['Content-Type' => 'image/png']
            ),
        ]);

        $data = $this->client->toUserData('access-token');

        $this->assertSame('Ada', $data['firstName']);
        $this->assertSame('Lovelace', $data['lastName']);
        $this->assertSame('ada@example.com', $data['email']);
        $this->assertSame('Software Engineer', $data['jobTitle']);
        $this->assertSame('https://www.linkedin.com/in/ada-lovelace', $data['portfolioUrl']);
        $this->assertSame('en', $data['language']);
        $this->assertStringStartsWith('cv-photos/', $data['photo']);
        $this->assertArrayNotHasKey('experiences', $data);
    }

    public function test_maps_dma_snapshot_positions_education_and_skills(): void
    {
        config(['services.linkedin.dma_enabled' => true]);

        Http::fake([
            'https://api.linkedin.com/v2/userinfo' => Http::response([
                'given_name' => 'Ada',
                'family_name' => 'Lovelace',
                'email' => 'ada@example.com',
            ]),
            'https://api.linkedin.com/v2/me*' => Http::response([], 403),
            'https://api.linkedin.com/rest/memberSnapshotData*' => function ($request) {
                parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);
                $domain = $query['domain'] ?? '';

                $payloads = [
                    'PROFILE' => [[
                        'Headline' => 'Mathematician',
                        'Summary' => 'Pioneer of computing.',
                    ]],
                    'POSITIONS' => [[
                        'Title' => 'Analyst',
                        'Company Name' => 'Analytical Engine',
                        'Location' => 'London',
                        'Description' => 'Wrote the first algorithm.',
                        'Started On' => '1842-01',
                        'Finished On' => 'Present',
                    ]],
                    'EDUCATION' => [[
                        'School Name' => 'Home study',
                        'Degree Name' => 'Mathematics',
                        'Field Of Study' => 'Algebra',
                        'Start Date' => ['year' => 1830, 'month' => 9],
                        'End Date' => ['year' => 1835, 'month' => 6],
                    ]],
                    'SKILLS' => [
                        ['Name' => 'Mathematics'],
                        ['Skill' => 'Programming'],
                    ],
                    'LANGUAGES' => [[
                        'Name' => 'English',
                        'Proficiency' => 'Native or bilingual',
                    ]],
                ];

                return Http::response([
                    'elements' => [
                        ['snapshotData' => $payloads[$domain] ?? []],
                    ],
                ]);
            },
        ]);

        $data = $this->client->toUserData('access-token');

        $this->assertSame('Mathematician', $data['jobTitle']);
        $this->assertSame('Pioneer of computing.', $data['summary']);
        $this->assertSame('Analyst', $data['experiences'][0]['position']);
        $this->assertSame('Analytical Engine', $data['experiences'][0]['company']);
        $this->assertTrue($data['experiences'][0]['current']);
        $this->assertSame('Home study', $data['educations'][0]['institution']);
        $this->assertSame('1830-09', $data['educations'][0]['from']);
        $this->assertSame('Mathematics', $data['skills'][0]['name']);
        $this->assertSame('English', $data['languages'][0]['name']);
        $this->assertSame(5, $data['languages'][0]['proficiencyLevel']);
    }

    public function test_falls_back_to_provided_userinfo_when_userinfo_endpoint_fails(): void
    {
        Http::fake([
            'https://api.linkedin.com/v2/userinfo' => Http::response([], 401),
            'https://api.linkedin.com/v2/me*' => Http::response([], 403),
        ]);

        $data = $this->client->toUserData('access-token', [
            'given_name' => 'Grace',
            'family_name' => 'Hopper',
            'email' => 'grace@example.com',
            'name' => 'Grace Hopper',
        ]);

        $this->assertSame('Grace', $data['firstName']);
        $this->assertSame('Hopper', $data['lastName']);
        $this->assertSame('grace@example.com', $data['email']);
    }
}
