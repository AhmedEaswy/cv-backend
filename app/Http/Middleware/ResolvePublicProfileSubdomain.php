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
        $slug = $this->profileDomain->slugFromHost($request->getHost());

        if ($slug === null) {
            return $next($request);
        }

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
            $profile = $this->repository->findPublicBySlug($slug);

            if (! $profile || ! $profile->enable_subdomain) {
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
