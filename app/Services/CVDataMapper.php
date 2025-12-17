<?php

namespace App\Services;

use App\Models\Profile;

class CVDataMapper
{
    /**
     * Map API user_data structure to Profile structure.
     */
    public function mapUserDataToProfile(array $userData): array
    {
        $mapped = [];

        // Map personal info
        if (!empty($userData)) {
            $mapped['info'] = [
                'firstName' => $userData['firstName'] ?? null,
                'lastName' => $userData['lastName'] ?? null,
                'jobTitle' => $userData['jobTitle'] ?? null,
                'email' => $userData['email'] ?? null,
                'address' => $userData['address'] ?? null,
                'portfolioUrl' => $userData['portfolioUrl'] ?? null,
                'phone' => $userData['phone'] ?? null,
                'summary' => $userData['summary'] ?? null,
                'birthdate' => $userData['birthdate'] ?? null,
            ];

            // Add skills to info if present
            if (isset($userData['skills'])) {
                $mapped['info']['skills'] = $userData['skills'];
            }
        }

        // Map educations (already in correct format)
        if (isset($userData['educations'])) {
            $mapped['educations'] = $userData['educations'];
        }

        // Map experiences: API uses "company", Profile uses "name"
        if (isset($userData['experiences'])) {
            $mapped['experiences'] = array_map(function ($exp) {
                return [
                    'position' => $exp['position'] ?? null,
                    'name' => $exp['company'] ?? null, // API: company -> Profile: name
                    'location' => $exp['location'] ?? null,
                    'description' => $exp['description'] ?? null,
                    'from' => $exp['from'] ?? null,
                    'to' => $exp['to'] ?? null,
                    'currentlyWorkingHere' => $exp['current'] ?? false, // API: current -> Profile: currentlyWorkingHere
                ];
            }, $userData['experiences']);
        }

        // Map projects: API uses "title", Profile uses "name"
        if (isset($userData['projects'])) {
            $mapped['projects'] = array_map(function ($proj) {
                return [
                    'name' => $proj['title'] ?? null, // API: title -> Profile: name
                    'description' => $proj['description'] ?? null,
                    'url' => $proj['url'] ?? null,
                    'from' => $proj['from'] ?? null,
                    'to' => $proj['to'] ?? null,
                ];
            }, $userData['projects']);
        }

        // Map languages: API uses "name" and "proficiencyLevel", Profile uses "language" and "level"
        if (isset($userData['languages'])) {
            $mapped['languages'] = array_map(function ($lang) {
                // Map proficiency level number to string
                $levelMap = [
                    1 => 'beginner',
                    2 => 'intermediate',
                    3 => 'advanced',
                    4 => 'fluent',
                    5 => 'native',
                ];
                return [
                    'language' => $lang['name'] ?? null, // API: name -> Profile: language
                    'level' => $levelMap[$lang['proficiencyLevel']] ?? 'beginner', // API: proficiencyLevel (1-5) -> Profile: level (string)
                ];
            }, $userData['languages']);
        }

        // Map interests: API uses array of objects with "name", Profile uses array of objects with "interest"
        if (isset($userData['interests'])) {
            $mapped['interests'] = array_map(function ($interest) {
                return [
                    'interest' => $interest['name'] ?? null, // API: name -> Profile: interest
                ];
            }, $userData['interests']);
        }

        return $mapped;
    }

    /**
     * Map Profile structure back to API user_data structure.
     */
    public function mapProfileToUserData(Profile $profile): array
    {
        $userData = [];

        // Map info back to user_data format
        if ($profile->info) {
            $info = $profile->info;
            $userData['firstName'] = $info['firstName'] ?? null;
            $userData['lastName'] = $info['lastName'] ?? null;
            $userData['jobTitle'] = $info['jobTitle'] ?? null;
            $userData['email'] = $info['email'] ?? null;
            $userData['address'] = $info['address'] ?? null;
            $userData['portfolioUrl'] = $info['portfolioUrl'] ?? null;
            $userData['phone'] = $info['phone'] ?? null;
            $userData['summary'] = $info['summary'] ?? null;
            $userData['birthdate'] = $info['birthdate'] ?? null;

            if (isset($info['skills'])) {
                $userData['skills'] = $info['skills'];
            }
        }

        // Map educations (already in correct format)
        if ($profile->educations) {
            $userData['educations'] = $profile->educations;
        }

        // Map experiences: Profile uses "name", API uses "company"
        if ($profile->experiences) {
            $userData['experiences'] = array_map(function ($exp) {
                return [
                    'position' => $exp['position'] ?? null,
                    'company' => $exp['name'] ?? null, // Profile: name -> API: company
                    'location' => $exp['location'] ?? null,
                    'description' => $exp['description'] ?? null,
                    'from' => $exp['from'] ?? null,
                    'to' => $exp['to'] ?? null,
                    'current' => $exp['currentlyWorkingHere'] ?? false, // Profile: currentlyWorkingHere -> API: current
                ];
            }, $profile->experiences);
        }

        // Map projects: Profile uses "name", API uses "title"
        if ($profile->projects) {
            $userData['projects'] = array_map(function ($proj) {
                return [
                    'title' => $proj['name'] ?? null, // Profile: name -> API: title
                    'description' => $proj['description'] ?? null,
                    'url' => $proj['url'] ?? null,
                    'from' => $proj['from'] ?? null,
                    'to' => $proj['to'] ?? null,
                ];
            }, $profile->projects);
        }

        // Map languages: Profile uses "language" and "level", API uses "name" and "proficiencyLevel"
        if ($profile->languages) {
            $levelMap = [
                'beginner' => 1,
                'intermediate' => 2,
                'advanced' => 3,
                'fluent' => 4,
                'native' => 5,
            ];
            $userData['languages'] = array_map(function ($lang) use ($levelMap) {
                return [
                    'name' => $lang['language'] ?? null, // Profile: language -> API: name
                    'proficiencyLevel' => $levelMap[$lang['level']] ?? 1, // Profile: level (string) -> API: proficiencyLevel (1-5)
                ];
            }, $profile->languages);
        }

        // Map interests: Profile uses "interest", API uses "name"
        if ($profile->interests) {
            $userData['interests'] = array_map(function ($interest) {
                return [
                    'name' => $interest['interest'] ?? null, // Profile: interest -> API: name
                ];
            }, $profile->interests);
        }

        return $userData;
    }

    /**
     * Format Profile to API response format.
     */
    public function formatProfileResponse(Profile $profile): array
    {
        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $profile->name,
            'language' => $profile->language,
            'sections_order' => $profile->sections_order,
            'user_data' => $this->mapProfileToUserData($profile),
            'created_at' => $profile->created_at?->toIso8601String(),
            'updated_at' => $profile->updated_at?->toIso8601String(),
        ];
    }
}

