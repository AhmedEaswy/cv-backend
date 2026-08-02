<?php

namespace App\Repositories;

use App\Models\PublicProfile;
use App\Models\PublicProfileTemplate;
use Illuminate\Database\Eloquent\Collection;

class PublicProfileRepository
{
    public function findForUser(int $userId): ?PublicProfile
    {
        return PublicProfile::where('user_id', $userId)->first();
    }

    public function findBySlug(string $slug): ?PublicProfile
    {
        return PublicProfile::where('slug', $slug)->first();
    }

    public function findPublicBySlug(string $slug): ?PublicProfile
    {
        return PublicProfile::where('slug', $slug)
            ->where('is_public', true)
            ->first();
    }

    public function create(array $data): PublicProfile
    {
        return PublicProfile::create($data);
    }

    public function update(PublicProfile $profile, array $data): PublicProfile
    {
        $profile->fill($data);
        $profile->save();

        return $profile;
    }

    public function delete(PublicProfile $profile): bool
    {
        return $profile->delete();
    }

    public function findActiveTemplate(int $id): ?PublicProfileTemplate
    {
        return PublicProfileTemplate::where('id', $id)
            ->where('is_active', true)
            ->first();
    }

    public function getActiveTemplates(): Collection
    {
        return PublicProfileTemplate::where('is_active', true)->get();
    }

    public function getDefaultTemplate(): ?PublicProfileTemplate
    {
        return PublicProfileTemplate::where('is_default', true)
            ->where('is_active', true)
            ->first()
            ?? PublicProfileTemplate::where('is_active', true)->first();
    }
}
