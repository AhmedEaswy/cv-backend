<?php

namespace App\Http\Middleware;

use App\Http\Controllers\PublicProfilePreviewController;
use App\Http\Requests\Portal\PortalContactMessageRequest;
use App\Repositories\PublicProfileRepository;
use App\Services\Profile\ProfileDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicProfileSubdomain
{
    public function __construct(
        private ProfileDomain $profileDomain,
        private PublicProfileRepository $repository,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $customProfile = $this->repository->findPublicByCustomDomain($request->getHost());
        if ($customProfile !== null) {
            return $this->serveProfileHost($request, $next, (string) $customProfile->slug, $customProfile);
        }

        $slug = $this->profileDomain->slugFromHost($request->getHost());

        if ($slug === null) {
            return $next($request);
        }

        return $this->serveProfileHost($request, $next, $slug, null);
    }

    private function serveProfileHost(Request $request, Closure $next, string $slug, ?\App\Models\PublicProfile $knownProfile): Response
    {

        $path = '/'.ltrim($request->path(), '/');
        $isRoot = $path === '/' || $path === '';
        $isContactGet = $path === '/contact';
        $isContactPost = $request->isMethod('POST') && ($path === '/contact' || $path === 'contact');

        if ($isContactPost) {
            /** @var PortalContactMessageRequest $formRequest */
            $formRequest = PortalContactMessageRequest::createFrom($request);
            $formRequest->setContainer(app());
            $formRequest->setRedirector(app('redirect'));
            $formRequest->validateResolved();

            return app(\App\Http\Controllers\Public\ContactMessageController::class)->store(
                $formRequest,
                $slug
            );
        }

        if ($isRoot || $isContactGet) {
            $profile = $knownProfile ?? $this->repository->findPublicBySlug($slug);

            if (! $profile) {
                abort(404, __('messages.public_profile_not_found'));
            }

            $subdomainAllowed = $profile->profileUrlMode() === 'subdomain' && $profile->enable_subdomain;
            $customDomainAllowed = $profile->profileUrlMode() === 'custom_domain'
                && $knownProfile !== null;

            if (! $subdomainAllowed && ! $customDomainAllowed) {
                abort(404, __('messages.public_profile_not_found'));
            }

            if ($isContactGet && ! $profile->showsContactForm()) {
                abort(404, __('messages.public_profile_not_found'));
            }

            $result = app(PublicProfilePreviewController::class)->preview($request, $slug);

            return $result instanceof Response
                ? $result
                : response($result);
        }

        return $next($request);
    }
}
