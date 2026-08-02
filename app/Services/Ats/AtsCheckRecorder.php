<?php

namespace App\Services\Ats;

use App\Models\AtsCheck;
use App\Models\Profile;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class AtsCheckRecorder
{
    public function __construct(
        private TrackingService $trackingService,
    ) {
    }

    /**
     * Persist an ATS check result for admin reporting.
     *
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>|null  $userData
     */
    public function record(
        Request $request,
        array $result,
        ?Profile $profile = null,
        ?array $userData = null,
        ?string $jobDescription = null,
        ?UploadedFile $pdf = null,
    ): AtsCheck {
        $tracking = $this->trackingService->capture($request);

        $name = $this->resolveCandidateName($profile, $userData, $result);
        $email = $this->resolveCandidateEmail($profile, $userData, $result);

        $jd = $jobDescription !== null ? trim($jobDescription) : '';
        $hasJd = $jd !== '';

        return AtsCheck::create(array_merge($tracking, [
            'user_id' => $request->user()?->id ?? $profile?->user_id,
            'profile_id' => $profile?->id,
            'source' => $result['source'] ?? 'structured',
            'score' => (int) ($result['score'] ?? 0),
            'grade' => (string) ($result['grade'] ?? 'F'),
            'language' => $request->input('language') ?? $profile?->language,
            'has_job_description' => $hasJd,
            'keyword_coverage' => $hasJd
                ? (int) data_get($result, 'keywords.coverage_percent', data_get($result, 'categories.keyword_fit'))
                : null,
            'categories' => $result['categories'] ?? null,
            'checks' => $result['checks'] ?? null,
            'keywords' => $result['keywords'] ?? null,
            'job_description' => $hasJd ? mb_substr($jd, 0, 5000) : null,
            'candidate_name' => $name,
            'candidate_email' => $email,
            'pdf_original_name' => $pdf?->getClientOriginalName(),
            'created_at' => now(),
        ]));
    }

    /**
     * @param  array<string, mixed>|null  $userData
     * @param  array<string, mixed>  $result
     */
    private function resolveCandidateName(?Profile $profile, ?array $userData, array $result): ?string
    {
        $first = $userData['firstName'] ?? $profile?->info['firstName'] ?? null;
        $last = $userData['lastName'] ?? $profile?->info['lastName'] ?? null;
        $name = trim(implode(' ', array_filter([(string) $first, (string) $last])));

        if ($name !== '') {
            return $name;
        }

        if (($result['source'] ?? null) === 'pdf') {
            return null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $userData
     * @param  array<string, mixed>  $result
     */
    private function resolveCandidateEmail(?Profile $profile, ?array $userData, array $result): ?string
    {
        $email = $userData['email'] ?? $profile?->info['email'] ?? null;

        if (is_string($email) && $email !== '') {
            return $email;
        }

        // Try to recover email from passed checks payload for PDF source
        foreach ($result['checks'] ?? [] as $check) {
            if (($check['id'] ?? null) === 'pdf_has_email' && ($check['passed'] ?? false)) {
                // Email exists in PDF but we don't store raw text — leave null
                break;
            }
        }

        return null;
    }
}
