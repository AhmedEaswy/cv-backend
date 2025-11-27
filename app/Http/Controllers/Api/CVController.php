<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'sections_order' => 'sometimes|array',
            'sections_order.*' => 'string',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'user_data' => 'sometimes|array',
            'user_data.firstName' => 'sometimes|string|max:255',
            'user_data.lastName' => 'sometimes|string|max:255',
            'user_data.jobTitle' => 'sometimes|string|max:255',
            'user_data.email' => 'sometimes|email|max:255',
            'user_data.address' => 'sometimes|nullable|string|max:500',
            'user_data.portfolioUrl' => 'sometimes|nullable|url|max:500',
            'user_data.phone' => 'sometimes|nullable|string|max:50',
            'user_data.summary' => 'sometimes|nullable|string',
            'user_data.birthdate' => 'sometimes|nullable|date',
            'user_data.skills' => 'sometimes|array',
            'user_data.skills.*.name' => 'required_with:user_data.skills|string|max:255',
            'user_data.educations' => 'sometimes|array',
            'user_data.educations.*.institution' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.degree' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.fieldOfStudy' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.educations.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences' => 'sometimes|array',
            'user_data.experiences.*.position' => 'required_with:user_data.experiences|string|max:255',
            'user_data.experiences.*.company' => 'sometimes|nullable|string|max:255',
            'user_data.experiences.*.description' => 'sometimes|nullable|string',
            'user_data.experiences.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences.*.current' => 'sometimes|boolean',
            'user_data.projects' => 'sometimes|array',
            'user_data.projects.*.title' => 'required_with:user_data.projects|string|max:255',
            'user_data.projects.*.description' => 'sometimes|nullable|string',
            'user_data.projects.*.technologies' => 'sometimes|nullable|string|max:500',
            'user_data.projects.*.url' => 'sometimes|nullable|url|max:500',
            'user_data.projects.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.projects.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.projects.*.current' => 'sometimes|boolean',
            'user_data.languages' => 'sometimes|array',
            'user_data.languages.*.name' => 'required_with:user_data.languages|string|max:255',
            'user_data.languages.*.proficiencyLevel' => 'required_with:user_data.languages|integer|min:1|max:5',
            'user_data.interests' => 'sometimes|array',
            'user_data.interests.*.name' => 'required_with:user_data.interests|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), 422, $validator->errors());
        }

        // Custom validation: Check date ranges for educations, experiences, and projects
        $errors = [];
        $userData = $request->input('user_data', []);

        if (isset($userData['educations']) && is_array($userData['educations'])) {
            foreach ($userData['educations'] as $index => $education) {
                if (isset($education['from']) && isset($education['to'])) {
                    if (strtotime($education['from']) > strtotime($education['to'])) {
                        $errors["user_data.educations.{$index}.to"] = ['The end date must be after or equal to the start date.'];
                    }
                }
            }
        }

        if (isset($userData['experiences']) && is_array($userData['experiences'])) {
            foreach ($userData['experiences'] as $index => $experience) {
                if (isset($experience['from']) && isset($experience['to']) && !($experience['current'] ?? false)) {
                    if (strtotime($experience['from']) > strtotime($experience['to'])) {
                        $errors["user_data.experiences.{$index}.to"] = ['The end date must be after or equal to the start date.'];
                    }
                }
            }
        }

        if (isset($userData['projects']) && is_array($userData['projects'])) {
            foreach ($userData['projects'] as $index => $project) {
                if (isset($project['from']) && isset($project['to']) && !($project['current'] ?? false)) {
                    if (strtotime($project['from']) > strtotime($project['to'])) {
                        $errors["user_data.projects.{$index}.to"] = ['The end date must be after or equal to the start date.'];
                    }
                }
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse(__('messages.validation_failed'), 422, $errors);
        }

        // Get user_id from authenticated user or request
        $userId = null;
        if ($request->user()) {
            $userId = $request->user()->id;
        } elseif ($request->has('user_id')) {
            $userId = $request->input('user_id');
        }

        // Map user_data to Profile structure
        $mappedData = $this->mapUserDataToProfile($userData);

        $profile = Profile::create([
            'user_id' => $userId,
            'name' => $request->input('name'),
            'language' => $request->input('language', 'en'),
            'sections_order' => $request->input('sections_order'),
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
    public function update(Request $request, string $id)
    {
        $user = $request->user();

        $profile = Profile::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        // Same validation as store, but all fields optional
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'sections_order' => 'sometimes|array',
            'sections_order.*' => 'string',
            'user_data' => 'sometimes|array',
            'user_data.firstName' => 'sometimes|string|max:255',
            'user_data.lastName' => 'sometimes|string|max:255',
            'user_data.jobTitle' => 'sometimes|string|max:255',
            'user_data.email' => 'sometimes|email|max:255',
            'user_data.address' => 'sometimes|nullable|string|max:500',
            'user_data.portfolioUrl' => 'sometimes|nullable|url|max:500',
            'user_data.phone' => 'sometimes|nullable|string|max:50',
            'user_data.summary' => 'sometimes|nullable|string',
            'user_data.birthdate' => 'sometimes|nullable|date',
            'user_data.skills' => 'sometimes|array',
            'user_data.skills.*.name' => 'required_with:user_data.skills|string|max:255',
            'user_data.educations' => 'sometimes|array',
            'user_data.educations.*.institution' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.degree' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.fieldOfStudy' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.educations.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences' => 'sometimes|array',
            'user_data.experiences.*.position' => 'required_with:user_data.experiences|string|max:255',
            'user_data.experiences.*.company' => 'sometimes|nullable|string|max:255',
            'user_data.experiences.*.description' => 'sometimes|nullable|string',
            'user_data.experiences.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences.*.current' => 'sometimes|boolean',
            'user_data.projects' => 'sometimes|array',
            'user_data.projects.*.title' => 'required_with:user_data.projects|string|max:255',
            'user_data.projects.*.description' => 'sometimes|nullable|string',
            'user_data.projects.*.technologies' => 'sometimes|nullable|string|max:500',
            'user_data.projects.*.url' => 'sometimes|nullable|url|max:500',
            'user_data.projects.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.projects.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.projects.*.current' => 'sometimes|boolean',
            'user_data.languages' => 'sometimes|array',
            'user_data.languages.*.name' => 'required_with:user_data.languages|string|max:255',
            'user_data.languages.*.proficiencyLevel' => 'required_with:user_data.languages|integer|min:1|max:5',
            'user_data.interests' => 'sometimes|array',
            'user_data.interests.*.name' => 'required_with:user_data.interests|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), 422, $validator->errors());
        }

        // Custom validation: Check date ranges
        $errors = [];
        $userData = $request->input('user_data', []);

        if (isset($userData['educations']) && is_array($userData['educations'])) {
            foreach ($userData['educations'] as $index => $education) {
                if (isset($education['from']) && isset($education['to'])) {
                    if (strtotime($education['from']) > strtotime($education['to'])) {
                        $errors["user_data.educations.{$index}.to"] = ['The end date must be after or equal to the start date.'];
                    }
                }
            }
        }

        if (isset($userData['experiences']) && is_array($userData['experiences'])) {
            foreach ($userData['experiences'] as $index => $experience) {
                if (isset($experience['from']) && isset($experience['to']) && !($experience['current'] ?? false)) {
                    if (strtotime($experience['from']) > strtotime($experience['to'])) {
                        $errors["user_data.experiences.{$index}.to"] = ['The end date must be after or equal to the start date.'];
                    }
                }
            }
        }

        if (isset($userData['projects']) && is_array($userData['projects'])) {
            foreach ($userData['projects'] as $index => $project) {
                if (isset($project['from']) && isset($project['to']) && !($project['current'] ?? false)) {
                    if (strtotime($project['from']) > strtotime($project['to'])) {
                        $errors["user_data.projects.{$index}.to"] = ['The end date must be after or equal to the start date.'];
                    }
                }
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse(__('messages.validation_failed'), 422, $errors);
        }

        // Update only provided fields
        if ($request->has('name')) {
            $profile->name = $request->input('name');
        }
        if ($request->has('language')) {
            $profile->language = $request->input('language');
        }
        if ($request->has('sections_order')) {
            $profile->sections_order = $request->input('sections_order');
        }
        if ($request->has('user_data')) {
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

    private function formatProfileResponse(Profile $profile): array
    {
        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $profile->name,
            'language' => $profile->language,
            'sections_order' => $profile->sections_order,
            'user_data' => $this->mapProfileToUserData($profile),
            'created_at' => $profile->created_at->toIso8601String(),
            'updated_at' => $profile->updated_at->toIso8601String(),
        ];
    }
}
