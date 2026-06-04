<?php

namespace Tests\Unit\Services;

use App\Models\Profile;
use App\Services\CVDataMapper;
use Tests\TestCase;

class CVDataMapperTest extends TestCase
{
    private CVDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new CVDataMapper();
    }

    public function test_map_user_data_to_profile_maps_basic_info(): void
    {
        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'jobTitle' => 'Software Engineer',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'address' => '123 Main St',
            'portfolioUrl' => 'https://john.dev',
            'summary' => 'Experienced developer',
            'birthdate' => '1990-01-15',
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertArrayHasKey('info', $result);
        $this->assertEquals('John', $result['info']['firstName']);
        $this->assertEquals('Doe', $result['info']['lastName']);
        $this->assertEquals('Software Engineer', $result['info']['jobTitle']);
        $this->assertEquals('john@example.com', $result['info']['email']);
        $this->assertEquals('123456789', $result['info']['phone']);
        $this->assertEquals('123 Main St', $result['info']['address']);
        $this->assertEquals('https://john.dev', $result['info']['portfolioUrl']);
        $this->assertEquals('Experienced developer', $result['info']['summary']);
        $this->assertEquals('1990-01-15', $result['info']['birthdate']);
    }

    public function test_map_user_data_to_profile_handles_skills(): void
    {
        $userData = [
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'Laravel'],
                ['name' => 'JavaScript'],
            ],
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('skills', $result['info']);
        $this->assertCount(3, $result['info']['skills']);
        $this->assertEquals('PHP', $result['info']['skills'][0]['name']);
    }

    public function test_map_user_data_to_profile_maps_educations(): void
    {
        $userData = [
            'educations' => [
                [
                    'institution' => 'MIT',
                    'degree' => 'Bachelor',
                    'fieldOfStudy' => 'Computer Science',
                    'from' => '2010-09',
                    'to' => '2014-06',
                ],
            ],
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertArrayHasKey('educations', $result);
        $this->assertCount(1, $result['educations']);
        $this->assertEquals('MIT', $result['educations'][0]['institution']);
        $this->assertEquals('Bachelor', $result['educations'][0]['degree']);
    }

    public function test_map_user_data_to_profile_maps_experiences(): void
    {
        $userData = [
            'experiences' => [
                [
                    'position' => 'Senior Developer',
                    'company' => 'Acme Corp',
                    'location' => 'New York',
                    'description' => 'Led team of 5',
                    'from' => '2020-01',
                    'to' => '2023-12',
                    'current' => false,
                ],
            ],
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertArrayHasKey('experiences', $result);
        $this->assertCount(1, $result['experiences']);
        // "company" in API maps to "name" in Profile
        $this->assertEquals('Acme Corp', $result['experiences'][0]['name']);
        $this->assertEquals('Senior Developer', $result['experiences'][0]['position']);
        // "current" in API maps to "currentlyWorkingHere" in Profile
        $this->assertFalse($result['experiences'][0]['currentlyWorkingHere']);
    }

    public function test_map_user_data_to_profile_maps_current_experience(): void
    {
        $userData = [
            'experiences' => [
                [
                    'position' => 'CTO',
                    'company' => 'Startup Inc',
                    'from' => '2024-01',
                    'current' => true,
                ],
            ],
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertTrue($result['experiences'][0]['currentlyWorkingHere']);
    }

    public function test_map_user_data_to_profile_maps_projects(): void
    {
        $userData = [
            'projects' => [
                [
                    'title' => 'My Project',
                    'description' => 'A great project',
                    'url' => 'https://project.dev',
                    'from' => '2023-01',
                    'to' => '2023-06',
                ],
            ],
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertArrayHasKey('projects', $result);
        // "title" in API maps to "name" in Profile
        $this->assertEquals('My Project', $result['projects'][0]['name']);
        $this->assertEquals('A great project', $result['projects'][0]['description']);
    }

    public function test_map_user_data_to_profile_maps_languages_with_level_conversion(): void
    {
        $userData = [
            'languages' => [
                ['name' => 'English', 'proficiencyLevel' => 5],
                ['name' => 'French', 'proficiencyLevel' => 3],
                ['name' => 'Spanish', 'proficiencyLevel' => 1],
            ],
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertCount(3, $result['languages']);
        // API "name" -> Profile "language"
        $this->assertEquals('English', $result['languages'][0]['language']);
        $this->assertEquals('native', $result['languages'][0]['level']);
        $this->assertEquals('French', $result['languages'][1]['language']);
        $this->assertEquals('advanced', $result['languages'][1]['level']);
        $this->assertEquals('Spanish', $result['languages'][2]['language']);
        $this->assertEquals('beginner', $result['languages'][2]['level']);
    }

    public function test_map_user_data_to_profile_maps_interests(): void
    {
        $userData = [
            'interests' => [
                ['name' => 'Photography'],
                ['name' => 'Hiking'],
            ],
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertCount(2, $result['interests']);
        // API "name" -> Profile "interest"
        $this->assertEquals('Photography', $result['interests'][0]['interest']);
        $this->assertEquals('Hiking', $result['interests'][1]['interest']);
    }

    public function test_map_user_data_to_profile_handles_empty_data(): void
    {
        $result = $this->mapper->mapUserDataToProfile([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_map_user_data_to_profile_handles_null_values(): void
    {
        $userData = [
            'firstName' => null,
            'lastName' => null,
        ];

        $result = $this->mapper->mapUserDataToProfile($userData);

        $this->assertArrayHasKey('info', $result);
        $this->assertNull($result['info']['firstName']);
        $this->assertNull($result['info']['lastName']);
    }

    public function test_map_profile_to_user_data_round_trip(): void
    {
        $originalUserData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'jobTitle' => 'Designer',
            'email' => 'jane@example.com',
            'skills' => [['name' => 'Figma']],
            'educations' => [
                ['institution' => 'UCLA', 'degree' => 'BA', 'fieldOfStudy' => 'Design'],
            ],
            'experiences' => [
                ['position' => 'UX Lead', 'company' => 'DesignCo', 'current' => true],
            ],
            'projects' => [
                ['title' => 'Redesign', 'description' => 'Full redesign'],
            ],
            'languages' => [
                ['name' => 'English', 'proficiencyLevel' => 5],
            ],
            'interests' => [
                ['name' => 'Art'],
            ],
        ];

        // Map to Profile format
        $profileData = $this->mapper->mapUserDataToProfile($originalUserData);

        // Create a profile model instance with this data
        $profile = new Profile($profileData);

        // Map back to user_data format
        $result = $this->mapper->mapProfileToUserData($profile);

        $this->assertEquals('Jane', $result['firstName']);
        $this->assertEquals('Smith', $result['lastName']);
        $this->assertEquals('Designer', $result['jobTitle']);
        $this->assertEquals('UX Lead', $result['experiences'][0]['position']);
        $this->assertEquals('DesignCo', $result['experiences'][0]['company']);
        $this->assertTrue($result['experiences'][0]['current']);
        $this->assertEquals('Redesign', $result['projects'][0]['title']);
        $this->assertEquals('English', $result['languages'][0]['name']);
        $this->assertEquals(5, $result['languages'][0]['proficiencyLevel']);
        $this->assertEquals('Art', $result['interests'][0]['name']);
    }
}
