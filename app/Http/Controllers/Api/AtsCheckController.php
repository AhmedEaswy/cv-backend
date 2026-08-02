<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AtsCheckRequest;
use App\Http\Requests\Api\AtsCheckUploadRequest;
use App\Repositories\CVRepositoryInterface;
use App\Services\Ats\AtsCheckerService;
use Illuminate\Support\Facades\App;

class AtsCheckController extends BaseApiController
{
    public function __construct(
        private AtsCheckerService $atsChecker,
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

        if ($request->filled('profile_id')) {
            $profile = $this->cvRepository->findById((int) $request->input('profile_id'));

            if (! $profile) {
                return $this->errorResponse(__('messages.profile_not_found'), 404);
            }

            $result = $this->atsChecker->checkProfile($profile, $jobDescription);
        } else {
            $result = $this->atsChecker->checkUserData(
                $request->input('user_data', []),
                $jobDescription
            );
        }

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

        $result = $this->atsChecker->checkPdf(
            $path,
            $request->input('job_description')
        );

        return $this->successResponse($result, __('ats.checked_successfully'));
    }
}
