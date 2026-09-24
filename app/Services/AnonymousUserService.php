<?php

namespace App\Services;

use App\Models\AnonymousUser;
use App\Models\CoverLetter;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnonymousUserService
{
    public function __construct(
        private TrackingService $trackingService
    ) {
    }

    /**
     * Resolve a valid UUID from the X-Anonymous-Id header or anonymous_id body field.
     */
    public function resolveId(Request $request): ?string
    {
        $raw = $request->header('X-Anonymous-Id')
            ?? $request->input('anonymous_id');

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $raw = trim($raw);

        return Str::isUuid($raw) ? Str::lower($raw) : null;
    }

    /**
     * Find or create the anonymous install and refresh last_seen_at + tracking.
     */
    public function resolve(Request $request): ?AnonymousUser
    {
        $id = $this->resolveId($request);

        if (! $id) {
            return null;
        }

        $tracking = $this->trackingService->capture($request);
        $now = now();

        $anonymousUser = AnonymousUser::query()->find($id);

        if ($anonymousUser) {
            $anonymousUser->fill(array_merge($tracking, [
                'last_seen_at' => $now,
            ]));
            $anonymousUser->save();

            return $anonymousUser;
        }

        return AnonymousUser::query()->create(array_merge([
            'id' => $id,
            'first_seen_at' => $now,
            'last_seen_at' => $now,
        ], $tracking));
    }

    public function clientRef(Request $request): ?string
    {
        $ref = $request->input('client_ref');

        if (! is_string($ref) || $ref === '') {
            return null;
        }

        $ref = trim($ref);

        return strlen($ref) <= 64 ? $ref : null;
    }

    /**
     * Find a guest-owned profile by server id or client_ref for this install.
     */
    public function findGuestProfile(
        AnonymousUser $anonymousUser,
        ?int $profileId = null,
        ?string $clientRef = null
    ): ?Profile {
        if ($profileId) {
            $profile = Profile::query()
                ->where('id', $profileId)
                ->whereNull('user_id')
                ->where('anonymous_user_id', $anonymousUser->id)
                ->first();

            if ($profile) {
                return $profile;
            }
        }

        if ($clientRef) {
            return Profile::query()
                ->whereNull('user_id')
                ->where('anonymous_user_id', $anonymousUser->id)
                ->where('client_ref', $clientRef)
                ->first();
        }

        return null;
    }

    /**
     * Find a guest-owned cover letter by server id or client_ref for this install.
     */
    public function findGuestCoverLetter(
        AnonymousUser $anonymousUser,
        ?int $coverLetterId = null,
        ?string $clientRef = null
    ): ?CoverLetter {
        if ($coverLetterId) {
            $coverLetter = CoverLetter::query()
                ->where('id', $coverLetterId)
                ->whereNull('user_id')
                ->where('anonymous_user_id', $anonymousUser->id)
                ->first();

            if ($coverLetter) {
                return $coverLetter;
            }
        }

        if ($clientRef) {
            return CoverLetter::query()
                ->whereNull('user_id')
                ->where('anonymous_user_id', $anonymousUser->id)
                ->where('client_ref', $clientRef)
                ->first();
        }

        return null;
    }

    /**
     * @return array{anonymous_user_id?: string, client_ref?: string}
     */
    public function ownershipAttributes(Request $request, ?AnonymousUser $anonymousUser = null): array
    {
        $anonymousUser ??= $this->resolve($request);

        if (! $anonymousUser) {
            return [];
        }

        $attrs = ['anonymous_user_id' => $anonymousUser->id];
        $clientRef = $this->clientRef($request);

        if ($clientRef) {
            $attrs['client_ref'] = $clientRef;
        }

        return $attrs;
    }
}
