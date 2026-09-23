<?php

namespace App\Services\LinkedIn;

use App\Services\CvPhotoService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LinkedInProfileClient
{
    /**
     * @var list<string>
     */
    private const SUPPORTED_LANGUAGES = ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'];

    /**
     * @var list<string>
     */
    private const DMA_DOMAINS = ['PROFILE', 'POSITIONS', 'EDUCATION', 'SKILLS', 'LANGUAGES'];

    public function __construct(private CvPhotoService $photos) {}

    /**
     * Build CV user_data from LinkedIn OpenID userinfo plus any extra
     * endpoints the app's LinkedIn products allow (headline, DMA snapshot).
     *
     * @param  array<string, mixed>  $fallbackUserinfo
     * @return array<string, mixed>
     */
    public function toUserData(string $accessToken, array $fallbackUserinfo = []): array
    {
        $userinfo = $this->getJson('https://api.linkedin.com/v2/userinfo', $accessToken) ?? [];
        if ($userinfo === []) {
            $userinfo = $fallbackUserinfo;
        }

        $me = $this->getJson('https://api.linkedin.com/v2/me', $accessToken, [
            'projection' => '(id,localizedFirstName,localizedLastName,localizedHeadline,vanityName)',
        ]);

        $snapshots = [];
        if (config('services.linkedin.dma_enabled')) {
            foreach (self::DMA_DOMAINS as $domain) {
                $snapshots[$domain] = $this->fetchSnapshot($accessToken, $domain);
            }
        }

        return $this->map($userinfo, $me, $snapshots, $accessToken);
    }

    /**
     * @param  array<string, mixed>  $userinfo
     * @param  array<string, mixed>|null  $me
     * @param  array<string, array<string, mixed>|null>  $snapshots
     * @return array<string, mixed>
     */
    private function map(array $userinfo, ?array $me, array $snapshots, string $accessToken): array
    {
        $profileRows = $this->snapshotRecords($snapshots['PROFILE'] ?? null);
        $profile = $profileRows[0] ?? [];

        $firstName = $this->firstNonEmpty(
            $userinfo['given_name'] ?? null,
            $me['localizedFirstName'] ?? null,
            $this->field($profile, 'First Name', 'firstName', 'first_name', 'given_name'),
        );
        $lastName = $this->firstNonEmpty(
            $userinfo['family_name'] ?? null,
            $me['localizedLastName'] ?? null,
            $this->field($profile, 'Last Name', 'lastName', 'last_name', 'family_name'),
        );

        if (($firstName === null || $lastName === null) && isset($userinfo['name'])) {
            $parts = preg_split('/\s+/u', trim((string) $userinfo['name']), 2) ?: [];
            $firstName ??= $parts[0] ?? null;
            $lastName ??= $parts[1] ?? null;
        }

        $headline = $this->firstNonEmpty(
            $me['localizedHeadline'] ?? null,
            $this->field($profile, 'Headline', 'headline'),
        );
        $summary = $this->field($profile, 'Summary', 'About', 'summary', 'about');
        $address = $this->field($profile, 'Address', 'Location', 'Geo Location', 'location');
        $phone = $this->field($profile, 'Phone Number', 'Phone', 'Mobile Phone', 'phone');

        $vanity = $me['vanityName'] ?? $this->field($profile, 'Vanity Name', 'Public Profile URL', 'vanityName');
        $portfolioUrl = null;
        if (is_string($vanity) && $vanity !== '') {
            $portfolioUrl = str_starts_with($vanity, 'http')
                ? $vanity
                : 'https://www.linkedin.com/in/'.$vanity;
        }

        $picture = $this->firstNonEmpty($userinfo['picture'] ?? null, $userinfo['picture_large'] ?? null);
        $photo = is_string($picture) && $picture !== ''
            ? ($this->photos->storeFromUrl($picture, $accessToken) ?? $picture)
            : null;

        $locale = (string) ($userinfo['locale'] ?? '');
        $language = strtolower(substr(str_replace('_', '-', $locale), 0, 2));
        if (! in_array($language, self::SUPPORTED_LANGUAGES, true)) {
            $language = null;
        }

        $userData = array_filter([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $this->firstNonEmpty($userinfo['email'] ?? null, $this->field($profile, 'Email Address', 'Email', 'email')),
            'jobTitle' => $headline,
            'summary' => $summary,
            'address' => $address,
            'phone' => $phone,
            'portfolioUrl' => $portfolioUrl,
            'photo' => $photo,
            'language' => $language,
        ], fn ($value) => $value !== null && $value !== '');

        $skills = $this->mapSkills($this->snapshotRecords($snapshots['SKILLS'] ?? null));
        if ($skills !== []) {
            $userData['skills'] = $skills;
        }

        $experiences = $this->mapPositions($this->snapshotRecords($snapshots['POSITIONS'] ?? null));
        if ($experiences !== []) {
            $userData['experiences'] = $experiences;
        }

        $educations = $this->mapEducations($this->snapshotRecords($snapshots['EDUCATION'] ?? null));
        if ($educations !== []) {
            $userData['educations'] = $educations;
        }

        $languages = $this->mapLanguages($this->snapshotRecords($snapshots['LANGUAGES'] ?? null));
        if ($languages !== []) {
            $userData['languages'] = $languages;
        }

        return $userData;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<array{name: string}>
     */
    private function mapSkills(array $rows): array
    {
        $skills = [];
        foreach ($rows as $row) {
            $name = $this->field($row, 'Name', 'Skill', 'Skill Name', 'name');
            if ($name !== null) {
                $skills[] = ['name' => $name];
            }
        }

        return $skills;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function mapPositions(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $position = $this->field($row, 'Title', 'Position', 'position');
            if ($position === null) {
                continue;
            }

            $to = $this->toYearMonth($this->field($row, 'Finished On', 'End Date', 'to'));
            $finished = $this->field($row, 'Finished On', 'End Date', 'to');
            $current = $finished === null
                || strcasecmp((string) $finished, 'Present') === 0
                || strcasecmp((string) $finished, 'Current') === 0;

            $items[] = [
                'position' => $position,
                'company' => $this->field($row, 'Company Name', 'Company', 'company'),
                'location' => $this->field($row, 'Location', 'location'),
                'description' => $this->field($row, 'Description', 'description'),
                'from' => $this->toYearMonth($this->field($row, 'Started On', 'Start Date', 'from')),
                'to' => $current ? null : $to,
                'current' => $current,
            ];
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function mapEducations(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $institution = $this->field($row, 'School Name', 'Institution', 'School', 'institution');
            $degree = $this->field($row, 'Degree Name', 'Degree', 'degree') ?? '';
            $fieldOfStudy = $this->field($row, 'Field Of Study', 'Notes', 'fieldOfStudy') ?? '';
            if ($institution === null) {
                continue;
            }

            $to = $this->toYearMonth($this->field($row, 'End Date', 'Finished On', 'to'));

            $items[] = [
                'institution' => $institution,
                'degree' => $degree !== '' ? $degree : '—',
                'fieldOfStudy' => $fieldOfStudy !== '' ? $fieldOfStudy : '—',
                'description' => $this->field($row, 'Notes', 'Description', 'description'),
                'from' => $this->toYearMonth($this->field($row, 'Start Date', 'Started On', 'from')),
                'to' => $to,
            ];
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<array{name: string, proficiencyLevel: int}>
     */
    private function mapLanguages(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $name = $this->field($row, 'Name', 'Language', 'name');
            if ($name === null) {
                continue;
            }

            $level = $this->field($row, 'Proficiency', 'Level', 'proficiency') ?? '';
            $items[] = [
                'name' => $name,
                'proficiencyLevel' => $this->proficiency($level),
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return list<array<string, mixed>>
     */
    private function snapshotRecords(?array $payload): array
    {
        if ($payload === null) {
            return [];
        }

        $records = [];
        foreach ($payload['elements'] ?? [] as $element) {
            if (! is_array($element)) {
                continue;
            }
            $chunk = $element['snapshotData'] ?? $element['data'] ?? null;
            if (! is_array($chunk)) {
                continue;
            }
            foreach ($chunk as $row) {
                if (is_array($row)) {
                    $records[] = $row;
                }
            }
        }

        return $records;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function field(array $row, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            foreach ($row as $k => $v) {
                if (strcasecmp((string) $k, $key) !== 0) {
                    continue;
                }
                if (is_array($v)) {
                    $nested = $this->toYearMonth($v);
                    if ($nested !== null) {
                        return $nested;
                    }

                    continue;
                }
                $value = trim((string) $v);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    private function toYearMonth(mixed $value): ?string
    {
        if (is_array($value)) {
            $year = $value['year'] ?? $value['Year'] ?? null;
            if ($year) {
                $month = max(1, min(12, (int) ($value['month'] ?? $value['Month'] ?? 1)));

                return sprintf('%04d-%02d', (int) $year, $month);
            }

            return null;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        if (preg_match('/^(\d{4})-(\d{2})/', $value, $matches)) {
            return $matches[1].'-'.$matches[2];
        }

        if (in_array(strtolower($value), ['present', 'current'], true)) {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date('Y-m', $timestamp);
    }

    private function proficiency(string $level): int
    {
        $level = strtolower($level);

        return match (true) {
            str_contains($level, 'native') => 5,
            str_contains($level, 'full') || str_contains($level, 'fluent') || str_contains($level, 'professional') => 4,
            str_contains($level, 'advanced') => 3,
            str_contains($level, 'limited') || str_contains($level, 'intermediate') => 2,
            default => 1,
        };
    }

    private function firstNonEmpty(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     */
    private function getJson(string $url, string $accessToken, array $query = []): ?array
    {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withToken($accessToken)
                ->get($url, $query);

            if (! $response->successful()) {
                return null;
            }

            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (Throwable $e) {
            Log::debug('LinkedIn profile request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchSnapshot(string $accessToken, string $domain): ?array
    {
        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->withToken($accessToken)
                ->withHeaders([
                    'LinkedIn-Version' => (string) config('services.linkedin.api_version', '202401'),
                    'X-Restli-Protocol-Version' => '2.0.0',
                ])
                ->get('https://api.linkedin.com/rest/memberSnapshotData', [
                    'q' => 'criteria',
                    'domain' => $domain,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (Throwable $e) {
            Log::debug('LinkedIn DMA snapshot failed', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
