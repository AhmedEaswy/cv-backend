<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AtsCheckRequest;
use App\Http\Requests\Api\AtsCheckUploadRequest;
use App\Repositories\CVRepositoryInterface;
use App\Services\Ats\AtsCheckerService;
use App\Services\Ats\AtsCheckRecorder;
use Illuminate\Support\Facades\App;

class AtsCheckController extends BaseApiController
{
    public function __construct(
        private AtsCheckerService $atsChecker,
        private AtsCheckRecorder $atsCheckRecorder,
        private CVRepositoryInterface $cvRepository,
    ) {
    }

    /**
     * Score structured CV data or an existing profile.
     */
    public function check(AtsCheckRequest $request)
    {
        if ($request->filled('language')) {
            App::setLocale($request->input('language'));
        }

        $jobDescription = $request->input('job_description');
        $profile = null;
        $userData = null;

        if ($request->filled('profile_id')) {
            $profile = $this->cvRepository->findById((int) $request->input('profile_id'));

            if (! $profile) {
                return $this->errorResponse(__('messages.profile_not_found'), 404);
            }

            $result = $this->atsChecker->checkProfile($profile, $jobDescription);
        } else {
            $userData = $request->input('user_data', []);
            $result = $this->atsChecker->checkUserData($userData, $jobDescription);
        }

        $record = $this->atsCheckRecorder->record(
            $request,
            $result,
            $profile,
            $userData,
            $jobDescription
        );

        $result['check_id'] = $record->id;

        return $this->successResponse($result, __('ats.checked_successfully'));
    }

    /**
     * Score an uploaded PDF resume.
     */
    public function checkUpload(AtsCheckUploadRequest $request)
    {
        if ($request->filled('language')) {
            App::setLocale($request->input('language'));
        }

        $file = $request->file('file');
        $path = $file->getRealPath();

        if ($path === false) {
            return $this->errorResponse(__('ats.errors.pdf_unreadable'), 422);
        }

        $jobDescription = $request->input('job_description');
        $result = $this->atsChecker->checkPdf($path, $jobDescription);

        $record = $this->atsCheckRecorder->record(
            $request,
            $result,
            null,
            null,
            $jobDescription,
            $file
        );

        $result['check_id'] = $record->id;

        return $this->successResponse($result, __('ats.checked_successfully'));
    }
}
