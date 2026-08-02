<?php

namespace App\Services\Ats;

class AtsRulesEngine
{
    /**
     * Run weighted ATS checks against a normalized CV payload.
     *
     * @param  array<string, mixed>  $cv
     * @return list<array{id: string, category: string, passed: bool, weight: int, message: string, tip: ?string}>
     */
    public function evaluate(array $cv, string $source): array
    {
        if ($source === 'pdf') {
            return $this->evaluatePdf($cv);
        }

        return $this->evaluateStructured($cv);
    }

    /**
     * @param  array<string, mixed>  $cv
     * @return list<array{id: string, category: string, passed: bool, weight: int, message: string, tip: ?string}>
     */
    private function evaluateStructured(array $cv): array
    {
        $checks = [];

        $firstName = trim((string) ($cv['firstName'] ?? ''));
        $lastName = trim((string) ($cv['lastName'] ?? ''));
        $hasName = $firstName !== '' || $lastName !== '';
        $checks[] = $this->check('has_name', 'completeness', $hasName);

        $jobTitle = trim((string) ($cv['jobTitle'] ?? ''));
        $checks[] = $this->check('has_job_title', 'completeness', $jobTitle !== '');

        $summary = trim((string) ($cv['summary'] ?? ''));
        $minSummary = (int) config('ats.thresholds.min_summary_length', 50);
        $checks[] = $this->check('has_summary', 'completeness', mb_strlen($summary) >= $minSummary);

        $experiences = $cv['experiences'] ?? [];
        $minExp = (int) config('ats.thresholds.min_experiences', 1);
        $checks[] = $this->check('has_experience', 'completeness', is_array($experiences) && count($experiences) >= $minExp);

        $educations = $cv['educations'] ?? [];
        $minEdu = (int) config('ats.thresholds.min_educations', 1);
        $checks[] = $this->check('has_education', 'completeness', is_array($educations) && count($educations) >= $minEdu);

        $skills = $cv['skills'] ?? [];
        $minSkills = (int) config('ats.thresholds.min_skills', 3);
        $skillCount = is_array($skills) ? count($skills) : 0;
        $checks[] = $this->check('has_skills', 'completeness', $skillCount >= $minSkills);

        $hasDates = false;
        if (is_array($experiences) && $experiences !== []) {
            $dated = 0;
            foreach ($experiences as $exp) {
                if (! empty($exp['from'])) {
                    $dated++;
                }
            }
            $hasDates = $dated === count($experiences);
        }
        $checks[] = $this->check('has_experience_dates', 'completeness', $hasDates);

        $email = trim((string) ($cv['email'] ?? ''));
        $checks[] = $this->check('has_email', 'contact', $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false);

        $phone = trim((string) ($cv['phone'] ?? ''));
        $checks[] = $this->check('has_phone', 'contact', $phone !== '');

        $address = trim((string) ($cv['address'] ?? ''));
        $checks[] = $this->check('has_address', 'contact', $address !== '');

        $minDesc = (int) config('ats.thresholds.min_experience_description_length', 40);
        $detailed = false;
        if (is_array($experiences)) {
            foreach ($experiences as $exp) {
                $desc = trim((string) ($exp['description'] ?? ''));
                if (mb_strlen($desc) >= $minDesc) {
                    $detailed = true;
                    break;
                }
            }
        }
        $checks[] = $this->check('experience_detail', 'content', $detailed);

        $experienceText = $this->experienceText($experiences);
        $checks[] = $this->check('action_verbs', 'content', $this->hasActionVerb($experienceText));
        $checks[] = $this->check('no_first_person', 'content', ! $this->hasFirstPerson($summary.' '.$experienceText));

        $hasPhoto = ! empty($cv['photo']);
        // Soft: pass when no photo (ATS-safer); fail (tip) when photo is set.
        $checks[] = $this->check('photo_soft_warning', 'ats_format', ! $hasPhoto);

        return $checks;
    }

    /**
     * @param  array<string, mixed>  $cv
     * @return list<array{id: string, category: string, passed: bool, weight: int, message: string, tip: ?string}>
     */
    private function evaluatePdf(array $cv): array
    {
        $checks = [];
        $text = (string) ($cv['raw_text'] ?? '');
        $charCount = (int) ($cv['pdf_meta']['char_count'] ?? mb_strlen($text));
        $fileSize = (int) ($cv['pdf_meta']['file_size'] ?? 0);
        $minText = (int) config('ats.thresholds.min_pdf_text_length', 80);

        $parseable = $charCount >= $minText;
        $checks[] = $this->check('pdf_parseable', 'ats_format', $parseable);

        if (! $parseable) {
            // Still emit remaining PDF checks as failed so weights stay consistent.
            $checks[] = $this->check('pdf_has_email', 'contact', false);
            $checks[] = $this->check('pdf_has_phone', 'contact', false);
            $checks[] = $this->check('pdf_section_headings', 'ats_format', false);
            $checks[] = $this->check('pdf_text_density', 'ats_format', false);
            $checks[] = $this->check('pdf_special_chars', 'ats_format', false);
            $checks[] = $this->check('has_summary', 'completeness', false);
            $checks[] = $this->check('has_experience', 'completeness', false);
            $checks[] = $this->check('has_skills', 'completeness', false);

            return $checks;
        }

        $lower = mb_strtolower($text);

        $hasEmail = (bool) preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text);
        $checks[] = $this->check('pdf_has_email', 'contact', $hasEmail);

        $hasPhone = (bool) preg_match('/(?:\+?\d[\d\s\-().]{7,}\d)/', $text);
        $checks[] = $this->check('pdf_has_phone', 'contact', $hasPhone);

        $headings = config('ats.section_headings', []);
        $foundHeadings = 0;
        foreach ($headings as $heading) {
            if (str_contains($lower, mb_strtolower((string) $heading))) {
                $foundHeadings++;
            }
        }
        $checks[] = $this->check('pdf_section_headings', 'ats_format', $foundHeadings >= 2);

        $minCharsPerKb = (int) config('ats.thresholds.min_chars_per_kb', 15);
        $sizeKb = max(1, (int) ceil($fileSize / 1024));
        $densityOk = ($charCount / $sizeKb) >= $minCharsPerKb;
        $checks[] = $this->check('pdf_text_density', 'ats_format', $densityOk);

        $special = preg_match_all('/[^\p{L}\p{N}\s.,;:\'\"()\-\/@+#]/u', $text) ?: 0;
        $ratio = $charCount > 0 ? $special / $charCount : 1;
        $maxRatio = (float) config('ats.thresholds.max_special_char_ratio', 0.25);
        $checks[] = $this->check('pdf_special_chars', 'ats_format', $ratio <= $maxRatio);

        // Soft content heuristics from raw text length / keywords
        $checks[] = $this->check('has_summary', 'completeness', $charCount >= 200);
        $hasExpHeading = str_contains($lower, 'experience')
            || str_contains($lower, 'الخبرة')
            || str_contains($lower, 'deneyim');
        $checks[] = $this->check('has_experience', 'completeness', $hasExpHeading);
        $hasSkillsHeading = str_contains($lower, 'skill')
            || str_contains($lower, 'المهارات')
            || str_contains($lower, 'beceri');
        $checks[] = $this->check('has_skills', 'completeness', $hasSkillsHeading);

        return $checks;
    }

    /**
     * @return array{id: string, category: string, passed: bool, weight: int, message: string, tip: ?string}
     */
    private function check(string $id, string $category, bool $passed): array
    {
        $weight = (int) config("ats.weights.{$id}", 1);

        return [
            'id' => $id,
            'category' => $category,
            'passed' => $passed,
            'weight' => $weight,
            'message' => __('ats.checks.'.$id.'.'.($passed ? 'pass' : 'fail')),
            'tip' => $passed ? null : __('ats.checks.'.$id.'.tip'),
        ];
    }

    /**
     * @param  mixed  $experiences
     */
    private function experienceText($experiences): string
    {
        if (! is_array($experiences)) {
            return '';
        }

        $parts = [];
        foreach ($experiences as $exp) {
            $parts[] = (string) ($exp['description'] ?? '');
            $parts[] = (string) ($exp['position'] ?? '');
        }

        return trim(implode(' ', $parts));
    }

    private function hasActionVerb(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        $lower = mb_strtolower($text);
        foreach (config('ats.action_verbs', []) as $verb) {
            if (preg_match('/\b'.preg_quote((string) $verb, '/').'\b/u', $lower)) {
                return true;
            }
        }

        return false;
    }

    private function hasFirstPerson(string $text): bool
    {
        $lower = ' '.mb_strtolower($text).' ';
        foreach (config('ats.first_person', []) as $pronoun) {
            if (str_contains($lower, (string) $pronoun)) {
                return true;
            }
        }

        return false;
    }
}
