<?php

namespace App\Services\Ats;

class AtsKeywordMatcher
{
    /**
     * Match job-description keywords against CV text.
     *
     * @return array{matched: list<string>, missing: list<string>, coverage_percent: int}
     */
    public function match(string $jobDescription, string $cvText): array
    {
        $terms = $this->extractTerms($jobDescription);

        if ($terms === []) {
            return [
                'matched' => [],
                'missing' => [],
                'coverage_percent' => 100,
            ];
        }

        $haystack = mb_strtolower($cvText);
        $matched = [];
        $missing = [];

        foreach ($terms as $term) {
            if ($this->termFound($term, $haystack)) {
                $matched[] = $term;
            } else {
                $missing[] = $term;
            }
        }

        $coverage = (int) round((count($matched) / count($terms)) * 100);

        return [
            'matched' => $matched,
            'missing' => $missing,
            'coverage_percent' => $coverage,
        ];
    }

    /**
     * @return list<string>
     */
    public function extractTerms(string $jobDescription): array
    {
        $minLength = (int) config('ats.thresholds.min_keyword_term_length', 3);
        $maxTerms = (int) config('ats.thresholds.max_keyword_terms', 40);
        $stopwords = array_flip(config('ats.stopwords', []));

        $normalized = mb_strtolower($jobDescription);
        $normalized = preg_replace('/[^a-z0-9+#.\-\/\s]/u', ' ', $normalized) ?? '';

        preg_match_all('/[a-z0-9+#.\-\/]+/u', $normalized, $matches);
        $tokens = $matches[0] ?? [];

        $counts = [];
        foreach ($tokens as $token) {
            $token = trim($token, '.-/');
            if (mb_strlen($token) < $minLength) {
                continue;
            }
            if (isset($stopwords[$token])) {
                continue;
            }
            if (ctype_digit($token)) {
                continue;
            }
            $counts[$token] = ($counts[$token] ?? 0) + 1;
        }

        arsort($counts);

        return array_slice(array_keys($counts), 0, $maxTerms);
    }

    private function termFound(string $term, string $haystack): bool
    {
        if (str_contains($term, '/') || str_contains($term, '-') || str_contains($term, '.')) {
            return str_contains($haystack, $term);
        }

        return (bool) preg_match('/\b'.preg_quote($term, '/').'\b/u', $haystack);
    }
}
