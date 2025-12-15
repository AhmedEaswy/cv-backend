<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PrintCVRequest;
use App\Http\Requests\Api\StoreCVRequest;
use App\Http\Requests\Api\UpdateCVRequest;
use App\Models\Profile;
use App\Models\Template;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class CVController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Profile::where('user_id', $user->id);

        // Filter by language if provided
        if ($request->has('language')) {
            $query->where('language', $request->input('language'));
        }

        $profiles = $query->get();

        $cvs = $profiles->map(fn ($profile) => $this->formatProfileResponse($profile));

        return $this->successResponse($cvs, __('messages.cvs_retrieved'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCVRequest $request)
    {
        $validated = $request->validated();

        // If unauthenticated user provides template_id, generate PDF instead of creating profile
        if (!$request->user() && $request->has('template_id')) {
            return $this->generatePdfFromRequest($request);
        }

        // Get user_id from authenticated user or request
        $userId = null;
        if ($request->user()) {
            $userId = $request->user()->id;
        } elseif ($request->has('user_id')) {
            $userId = $request->input('user_id');
        }

        // Map user_data to Profile structure
        $userData = $request->input('user_data', []);
        $mappedData = $this->mapUserDataToProfile($userData);

        $profile = Profile::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'language' => $validated['language'] ?? 'en',
            'sections_order' => $validated['sections_order'] ?? null,
            'info' => $mappedData['info'] ?? null,
            'interests' => $mappedData['interests'] ?? null,
            'languages' => $mappedData['languages'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
            'projects' => $mappedData['projects'] ?? null,
            'educations' => $mappedData['educations'] ?? null,
        ]);

        // Return response in API format
        return $this->successResponse(
            $this->formatProfileResponse($profile),
            __('messages.cv_created'),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $profile = Profile::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        return $this->successResponse($this->formatProfileResponse($profile), __('messages.cv_retrieved'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCVRequest $request, string $id)
    {
        $user = $request->user();

        $profile = Profile::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        $validated = $request->validated();

        // Update only provided fields
        if (isset($validated['name'])) {
            $profile->name = $validated['name'];
        }
        if (isset($validated['language'])) {
            $profile->language = $validated['language'];
        }
        if (isset($validated['sections_order'])) {
            $profile->sections_order = $validated['sections_order'];
        }
        if (isset($validated['user_data'])) {
            $userData = $validated['user_data'];
            $mappedData = $this->mapUserDataToProfile($userData);
            if (isset($mappedData['info'])) {
                $profile->info = $mappedData['info'];
            }
            if (isset($mappedData['interests'])) {
                $profile->interests = $mappedData['interests'];
            }
            if (isset($mappedData['languages'])) {
                $profile->languages = $mappedData['languages'];
            }
            if (isset($mappedData['experiences'])) {
                $profile->experiences = $mappedData['experiences'];
            }
            if (isset($mappedData['projects'])) {
                $profile->projects = $mappedData['projects'];
            }
            if (isset($mappedData['educations'])) {
                $profile->educations = $mappedData['educations'];
            }
        }

        $profile->save();

        return $this->successResponse(
            $this->formatProfileResponse($profile),
            __('messages.cv_updated')
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $profile = Profile::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        $profile->delete();

        return $this->successResponse(null, __('messages.cv_deleted'));
    }

    /**
     * Map API user_data structure to Profile structure
     */
    private function mapUserDataToProfile(array $userData): array
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
     * Map Profile structure back to API user_data structure
     */
    private function mapProfileToUserData(Profile $profile): array
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
     * Generate PDF from CV data.
     */
    public function print(PrintCVRequest $request)
    {
        $templateId = $request->input('template_id');
        $profileId = $request->input('profile_id');

        // Load existing profile or create temporary one
        if ($profileId) {
            $user = $request->user();
            $profile = Profile::where('id', $profileId);

            if ($user) {
                $profile->where('user_id', $user->id);
            }

            $profile = $profile->first();

            if (!$profile) {
                return $this->errorResponse(__('messages.cv_not_found'), 404);
            }
        } else {
            // Create temporary profile from user_data
            $userData = $request->input('user_data', []);
            $mappedData = $this->mapUserDataToProfile($userData);

            $profile = new Profile([
                'user_id' => $request->user()?->id,
                'name' => $request->input('name', 'CV'),
                'language' => $request->input('language', 'en'),
                'sections_order' => $request->input('sections_order'),
                'info' => $mappedData['info'] ?? null,
                'interests' => $mappedData['interests'] ?? null,
                'languages' => $mappedData['languages'] ?? null,
                'experiences' => $mappedData['experiences'] ?? null,
                'projects' => $mappedData['projects'] ?? null,
                'educations' => $mappedData['educations'] ?? null,
            ]);
        }

        // Load template
        $template = Template::where('id', $templateId)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return $this->errorResponse(__('messages.template_not_found_or_inactive'), 404);
        }

        // Convert template name to view name (kebab-case)
        $viewName = strtolower(str_replace(' ', '-', $template->name));
        $viewPath = "templates.cv.{$viewName}";

        // Check if view exists
        if (!view()->exists($viewPath)) {
            \Log::error('PDF Generation: View not found', [
                'view_path' => $viewPath,
                'template_id' => $templateId,
                'template_name' => $template->name ?? 'N/A',
            ]);
            return $this->errorResponse(
                __('messages.pdf_generation_failed') . ': Template view not found. Please check server configuration.',
                500
            );
        }

        // Format profile data for view
        $cvData = $this->formatProfileResponse($profile);

        // Generate PDF
        try {
            $pdf = Pdf::view($viewPath, ['cv' => $cvData])
                ->format('a4')
                ->margins(10, 10, 10, 10)
                ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                    $browsershot->setOption('args', [
                        '--no-sandbox',
                        '--disable-dev-shm-usage',
                    ]);
                });

            $filename = ($cvData['user_data']['firstName'] ?? 'CV') . '_' . ($cvData['user_data']['lastName'] ?? 'Resume') . '.pdf';

            return $pdf->download($filename);
        } catch (\Illuminate\View\ViewException $e) {
            \Log::error('PDF Generation: View Exception', [
                'message' => $e->getMessage(),
                'view_path' => $viewPath,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->errorResponse(
                __('messages.pdf_generation_failed') . ': View error - ' . $e->getMessage(),
                500
            );
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            \Log::error('PDF Generation: Process Failed', [
                'message' => $e->getMessage(),
                'command' => method_exists($e->getProcess(), 'getCommandLine') ? $e->getProcess()->getCommandLine() : 'N/A',
            ]);
            return $this->errorResponse(
                __('messages.pdf_generation_failed') . ': PDF generation process failed. Please ensure Chrome/Chromium and Node.js are installed on the server.',
                500
            );
        } catch (\Exception $e) {
            \Log::error('PDF Generation: General Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class' => get_class($e),
            ]);

            $errorMessage = __('messages.pdf_generation_failed');
            if (app()->environment('production')) {
                $errorMessage .= '. Please contact support if this issue persists.';
            } else {
                $errorMessage .= ': ' . $e->getMessage();
            }

            return $this->errorResponse($errorMessage, 500);
        }
    }

    /**
     * Generate PDF from request data (used in store method for unauthenticated users).
     */
    private function generatePdfFromRequest(Request $request)
    {
        try {
            $templateId = $request->input('template_id');
            $userData = $request->input('user_data', []);
            $mappedData = $this->mapUserDataToProfile($userData);

            // Create temporary profile
            $profile = new Profile([
                'user_id' => null,
                'name' => $request->input('name', 'CV'),
                'language' => $request->input('language', 'en'),
                'sections_order' => $request->input('sections_order'),
                'info' => $mappedData['info'] ?? null,
                'interests' => $mappedData['interests'] ?? null,
                'languages' => $mappedData['languages'] ?? null,
                'experiences' => $mappedData['experiences'] ?? null,
                'projects' => $mappedData['projects'] ?? null,
                'educations' => $mappedData['educations'] ?? null,
            ]);

            // Load template
            $template = Template::where('id', $templateId)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return $this->errorResponse(__('messages.template_not_found_or_inactive'), 404);
            }

            // Convert template name to view name (kebab-case)
            $viewName = strtolower(str_replace(' ', '-', $template->name));
            $viewPath = "templates.cv.{$viewName}";

            // Check if view exists
            if (!view()->exists($viewPath)) {
                \Log::error('PDF Generation: View not found', [
                    'view_path' => $viewPath,
                    'template_id' => $templateId,
                    'template_name' => $template->name,
                    'view_name' => $viewName,
                ]);
                return $this->errorResponse(
                    __('messages.pdf_generation_failed') . ': Template view not found. Please check server configuration.',
                    500
                );
            }

            // Format profile data for view
            $cvData = $this->formatProfileResponse($profile);

            // Generate PDF
            $pdf = Pdf::view($viewPath, ['cv' => $cvData])
                ->format('a4')
                ->margins(10, 10, 10, 10)
                ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                    $browsershot->setOption('args', [
                        '--no-sandbox',
                        '--disable-dev-shm-usage',
                    ]);
                });

            $filename = ($cvData['user_data']['firstName'] ?? 'CV') . '_' . ($cvData['user_data']['lastName'] ?? 'Resume') . '.pdf';

            return $pdf->download($filename);
        } catch (\Illuminate\View\ViewException $e) {
            \Log::error('PDF Generation: View Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse(
                __('messages.pdf_generation_failed') . ': View error - ' . $e->getMessage(),
                500
            );
        } catch (\Spatie\Pdf\Exceptions\InvalidFormat $e) {
            \Log::error('PDF Generation: Invalid Format', [
                'message' => $e->getMessage(),
            ]);
            return $this->errorResponse(
                __('messages.pdf_generation_failed') . ': Invalid PDF format - ' . $e->getMessage(),
                500
            );
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            \Log::error('PDF Generation: Process Failed', [
                'message' => $e->getMessage(),
                'command' => method_exists($e->getProcess(), 'getCommandLine') ? $e->getProcess()->getCommandLine() : 'N/A',
            ]);
            return $this->errorResponse(
                __('messages.pdf_generation_failed') . ': PDF generation process failed. Please ensure Chrome/Chromium and Node.js are installed on the server.',
                500
            );
        } catch (\Exception $e) {
            \Log::error('PDF Generation: General Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
            ]);

            // Provide user-friendly error message
            $errorMessage = __('messages.pdf_generation_failed');
            if (app()->environment('production')) {
                // In production, don't expose full error details
                $errorMessage .= '. Please contact support if this issue persists.';
            } else {
                // In development, show detailed error
                $errorMessage .= ': ' . $e->getMessage();
            }

            return $this->errorResponse($errorMessage, 500);
        }
    }

    private function formatProfileResponse(Profile $profile): array
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
