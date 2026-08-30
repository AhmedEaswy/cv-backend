<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Repositories\CVRepositoryInterface;
use App\Services\Ats\AtsCheckerService;
use App\Services\Ats\AtsCheckRecorder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\App;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Score a CV against ATS rules. Pass user_data JSON or profile_id, optionally with a job_description.')]
class CheckAtsTool extends Tool
{
    use InteractsWithToolPayload;

    public function __construct(
        private AtsCheckerService $atsChecker,
        private AtsCheckRecorder $atsCheckRecorder,
        private CVRepositoryInterface $cvRepository,
    ) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'profile_id' => 'sometimes|nullable|integer',
            'user_data' => 'sometimes|nullable',
            'job_description' => 'sometimes|nullable|string|max:20000',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
        ]);

        if (! empty($validated['language'])) {
            App::setLocale($validated['language']);
        }

        $jobDescription = $validated['job_description'] ?? null;
        $profileId = $validated['profile_id'] ?? null;
        $userData = $this->decodeUserData($request);

        if ($profileId) {
            $profile = $this->cvRepository->findById((int) $profileId);

            if (! $profile) {
                return $this->fail(__('messages.profile_not_found'));
            }

            $result = $this->atsChecker->checkProfile($profile, $jobDescription);
            $record = $this->atsCheckRecorder->record(request(), $result, $profile, null, $jobDescription);
        } else {
            if ($userData === []) {
                return $this->fail(__('ats.errors.profile_or_user_data_required'));
            }

            $result = $this->atsChecker->checkUserData($userData, $jobDescription);
            $record = $this->atsCheckRecorder->record(request(), $result, null, $userData, $jobDescription);
        }

        $result['check_id'] = $record->id;

        return $this->ok($result, __('ats.checked_successfully'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'user_data' => $schema->string()->description('JSON object of CV fields (firstName, lastName, jobTitle, email, summary, skills, experiences, educations, …).'),
            'profile_id' => $schema->integer()->description('Existing CV profile id to score instead of user_data.'),
            'job_description' => $schema->string()->description('Optional job description for keyword matching.'),
            'language' => $schema->string()->description('Locale for check messages: en, ar, or tr.')->default('en'),
        ];
    }
}
