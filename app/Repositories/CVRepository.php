<?php

namespace App\Repositories;

use App\Models\Profile;
use App\Models\Template;
use Illuminate\Database\Eloquent\Collection;

class CVRepository implements CVRepositoryInterface
{
    /**
     * Get all CVs for a user.
     */
    public function getAllForUser(int $userId, ?string $language = null): Collection
    {
        $query = Profile::where('user_id', $userId);

        if ($language) {
            $query->where('language', $language);
        }

        return $query->get();
    }

    /**
     * Find a CV by ID for a specific user.
     */
    public function findByIdForUser(int $id, int $userId): ?Profile
    {
        return Profile::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Create a new CV.
     */
    public function create(array $data): Profile
    {
        return Profile::create($data);
    }

    /**
     * Update a CV.
     */
    public function update(Profile $profile, array $data): Profile
    {
        $profile->fill($data);
        $profile->save();

        return $profile;
    }

    /**
     * Delete a CV (soft delete).
     */
    public function delete(Profile $profile): bool
    {
        return $profile->delete();
    }

    /**
     * Find a CV by ID (for public/unauthenticated access).
     */
    public function findById(int $id): ?Profile
    {
        return Profile::find($id);
    }

    /**
     * Find an active template by ID.
     */
    public function findActiveTemplate(int $id): ?Template
    {
        return Template::where('id', $id)
            ->where('is_active', true)
            ->first();
    }
}

