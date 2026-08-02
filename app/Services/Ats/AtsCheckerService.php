<?php

namespace App\Services\Ats;

use App\Models\Profile;
use App\Services\CVDataMapper;

class AtsCheckerService
{
    public function __construct(
        private CVDataMapper $dataMapper,
        private AtsRulesEngine $rulesEngine,
        private AtsKeywordMatcher $keywordMatcher,
        private PdfTextExtractor $pdfExtractor,
    ) {
    }

    /**
     * Score structured CV data (API user_data shape).
     *
     * @param  array<string, mixed>  $userData
     * @return array<string, mixed>
     */
    public function checkUserData(array $userData, ?string $jobDescription = null): array
    {
        $cv = $this->normalizeUserData($userData);

        return $this->score($cv, 'structured', $jobDescription);
    }

    /**
     * Score an existing Profile model.
     *
     * @return array<string, mixed>
     */
    public function checkProfile(Profile $profile, ?string $jobDescription = null): array
    {
        $userData = $this->dataMapper->mapProfileToUserData($profile);

        return $this->checkUserData($userData, $jobDescription);
    }

    /**
     * Score an uploaded PDF resume.
     *
     * @return array<string, mixed>
     */
    public function checkPdf(string $absolutePath, ?string $jobDescription = null): array
    {
        $extracted = $this->pdfExtractor->extract($absolutePath);

        $cv = [
            'raw_text' => $extracted['text'],
            'pdf_meta' => [
                'page_count' => $extracted['page_count'],
                'file_size' => $extracted['file_size'],
                'char_count' => $extracted['char_count'],
            ],
        ];

        return $this->score($cv, 'pdf', $jobDescription);
    }

    /**
     * @param  array<string, mixed>  $cv
     * @return array<string, mixed>
     */
    private function score(array $cv, string $source, ?string $jobDescription): array
    {
        $checks = $this->rulesEngine->evaluate($cv, $source);
        $categories = $this->categoryScores($checks);

        $totalWeight = 0;
        $earnedWeight = 0;
        foreach ($checks as $check) {
            $totalWeight += $check['weight'];
            if ($check['passed']) {
                $earnedWeight += $check['weight'];
            }
        }

        $baseScore = $totalWeight > 0
            ? (int) round(($earnedWeight / $totalWeight) * 100)
            : 0;

        $keywords = null;
        $overall = $baseScore;

        if ($jobDescription !== null && trim($jobDescription) !== '') {
            $cvText = $this->cvTextForKeywords($cv, $source);
            $keywords = $this->keywordMatcher->match($jobDescription, $cvText);
            $coverage = $keywords['coverage_percent'];
            $categories['keyword_fit'] = $coverage;

            $structural = (float) config('ats.structural_weight', 0.7);
            $keywordWeight = (float) config('ats.keyword_weight', 0.3);
            $overall = (int) round(($structural * $baseScore) + ($keywordWeight * $coverage));
        }

        return [
            'score' => max(0, min(100, $overall)),
            'grade' => $this->grade($overall),
            'source' => $source,
            'categories' => $categories,
            'checks' => $checks,
            'keywords' => $keywords,
        ];
    }

    /**
     * @param  list<array{category: string, passed: bool, weight: int}>  $checks
     * @return array<string, int>
     */
    private function categoryScores(array $checks): array
    {
        $buckets = [];

        foreach ($checks as $check) {
            $cat = $check['category'];
            if (! isset($buckets[$cat])) {
                $buckets[$cat] = ['earned' => 0, 'total' => 0];
            }
            $buckets[$cat]['total'] += $check['weight'];
            if ($check['passed']) {
                $buckets[$cat]['earned'] += $check['weight'];
            }
        }

        $scores = [];
        foreach ($buckets as $cat => $data) {
            $scores[$cat] = $data['total'] > 0
                ? (int) round(($data['earned'] / $data['total']) * 100)
                : 0;
        }

        return $scores;
    }

    private function grade(int $score): string
    {
        $bands = config('ats.grades', [
            'A' => 85,
            'B' => 70,
            'C' => 55,
            'D' => 40,
        ]);

        foreach ($bands as $letter => $threshold) {
            if ($score >= (int) $threshold) {
                return (string) $letter;
            }
        }

        return 'F';
    }

    /**
     * @param  array<string, mixed>  $userData
     * @return array<string, mixed>
     */
    private function normalizeUserData(array $userData): array
    {
        $experiences = [];
        foreach ($userData['experiences'] ?? [] as $exp) {
            $experiences[] = [
                'position' => $exp['position'] ?? null,
                'company' => $exp['company'] ?? ($exp['name'] ?? null),
                'description' => $exp['description'] ?? null,
                'from' => $exp['from'] ?? null,
                'to' => $exp['to'] ?? null,
                'current' => $exp['current'] ?? ($exp['currentlyWorkingHere'] ?? false),
            ];
        }

        return [
            'firstName' => $userData['firstName'] ?? null,
            'lastName' => $userData['lastName'] ?? null,
            'jobTitle' => $userData['jobTitle'] ?? null,
            'email' => $userData['email'] ?? null,
            'phone' => $userData['phone'] ?? null,
            'address' => $userData['address'] ?? null,
            'summary' => $userData['summary'] ?? null,
            'photo' => $userData['photo'] ?? null,
            'skills' => $userData['skills'] ?? [],
            'experiences' => $experiences,
            'educations' => $userData['educations'] ?? [],
            'projects' => $userData['projects'] ?? [],
            'languages' => $userData['languages'] ?? [],
            'interests' => $userData['interests'] ?? [],
            'raw_text' => $this->flattenUserDataText($userData, $experiences),
        ];
    }

    /**
     * @param  array<string, mixed>  $userData
     * @param  list<array<string, mixed>>  $experiences
     */
    private function flattenUserDataText(array $userData, array $experiences): string
    {
        $parts = [
            $userData['firstName'] ?? '',
            $userData['lastName'] ?? '',
            $userData['jobTitle'] ?? '',
            $userData['email'] ?? '',
            $userData['phone'] ?? '',
            $userData['address'] ?? '',
            $userData['summary'] ?? '',
        ];

        foreach ($userData['skills'] ?? [] as $skill) {
            $parts[] = is_array($skill) ? ($skill['name'] ?? '') : (string) $skill;
        }

        foreach ($experiences as $exp) {
            $parts[] = $exp['position'] ?? '';
            $parts[] = $exp['company'] ?? '';
            $parts[] = $exp['description'] ?? '';
        }

        foreach ($userData['educations'] ?? [] as $edu) {
            $parts[] = $edu['institution'] ?? '';
            $parts[] = $edu['degree'] ?? '';
            $parts[] = $edu['fieldOfStudy'] ?? '';
            $parts[] = $edu['description'] ?? '';
        }

        foreach ($userData['projects'] ?? [] as $proj) {
            $parts[] = $proj['title'] ?? ($proj['name'] ?? '');
            $parts[] = $proj['description'] ?? '';
            $parts[] = $proj['technologies'] ?? '';
        }

        foreach ($userData['languages'] ?? [] as $lang) {
            $parts[] = $lang['name'] ?? ($lang['language'] ?? '');
        }

        foreach ($userData['interests'] ?? [] as $interest) {
            $parts[] = is_array($interest) ? ($interest['name'] ?? ($interest['interest'] ?? '')) : (string) $interest;
        }

        return trim(implode(' ', array_filter(array_map(
            static fn ($p) => trim((string) $p),
            $parts
        ))));
    }

    /**
     * @param  array<string, mixed>  $cv
     */
    private function cvTextForKeywords(array $cv, string $source): string
    {
        if ($source === 'pdf') {
            return (string) ($cv['raw_text'] ?? '');
        }

        return (string) ($cv['raw_text'] ?? '');
    }
}
