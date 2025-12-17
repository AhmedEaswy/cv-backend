<?php

namespace App\Repositories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Collection;

interface CVRepositoryInterface
{
    /**
     * Get all CVs for a user.
     */
    public function getAllForUser(int $userId, ?string $language = null): Collection;

    /**
     * Find a CV by ID for a specific user.
     */
    public function findByIdForUser(int $id, int $userId): ?Profile;

    /**
     * Create a new CV.
     */
    public function create(array $data): Profile;

    /**
     * Update a CV.
     */
    public function update(Profile $profile, array $data): Profile;

    /**
     * Delete a CV (soft delete).
     */
    public function delete(Profile $profile): bool;

    /**
     * Find a CV by ID (for public/unauthenticated access).
     */
    public function findById(int $id): ?Profile;

    /**
     * Find an active template by ID.
     */
    public function findActiveTemplate(int $id): ?\App\Models\Template;
}

