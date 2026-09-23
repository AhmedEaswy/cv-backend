<?php

namespace App\Services\LinkedIn;

use App\Models\Profile;
use App\Models\Template;
use App\Models\User;
use App\Repositories\CVRepositoryInterface;
use App\Services\CVDataMapper;
use App\Services\TrackingService;
use Illuminate\Http\Request;

class LinkedInCvImporter
{
    public function __construct(
        private LinkedInProfileClient $client,
        private CVDataMapper $mapper,
        private CVRepositoryInterface $cvs,
        private TrackingService $tracking,
    ) {}

    /**
     * @param  array<string, mixed>  $fallbackUserinfo
     */
    public function importForUser(User $user, string $accessToken, array $fallbackUserinfo = [], ?Request $request = null): Profile
    {
        $userData = $this->client->toUserData($accessToken, $fallbackUserinfo);
        $mapped = $this->mapper->mapUserDataToProfile($userData);

        $info = $mapped['info'] ?? [];
        if (($info['firstName'] ?? null) === null && ($info['lastName'] ?? null) === null) {
            $info['firstName'] = $user->first_name ?: $user->name;
            $info['lastName'] = $user->last_name;
            $info['email'] = $info['email'] ?? $user->email;
            $mapped['info'] = $info;
        }

        $tracking = $request ? $this->tracking->capture($request) : [
            'ip_address' => null,
            'country' => null,
            'device' => null,
        ];

        $firstName = trim((string) ($mapped['info']['firstName'] ?? ''));
        $cvName = $firstName !== ''
            ? __('portal.cvs.from_linkedin_named', ['name' => $firstName])
            : __('portal.cvs.from_linkedin');

        $language = $userData['language'] ?? app()->getLocale();
        if (! in_array($language, ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'], true)) {
            $language = 'en';
        }

        $templateId = Template::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->value('id');

        return $this->cvs->create(array_merge([
            'user_id' => $user->id,
            'name' => $cvName,
            'language' => $language,
            'template_id' => $templateId,
            'is_public' => false,
            'info' => $mapped['info'] ?? null,
            'interests' => $mapped['interests'] ?? null,
            'languages' => $mapped['languages'] ?? null,
            'experiences' => $mapped['experiences'] ?? null,
            'projects' => $mapped['projects'] ?? null,
            'educations' => $mapped['educations'] ?? null,
        ], $tracking));
    }

    public function shouldAutoImport(User $user): bool
    {
        return ! $user->profiles()->exists();
    }
}
