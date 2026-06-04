<?php

namespace App\Repositories;

use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use Illuminate\Database\Eloquent\Collection;

class CoverLetterRepository
{
    public function getAllForUser(int $userId, ?string $language = null): Collection
    {
        $query = CoverLetter::where('user_id', $userId);

        if ($language) {
            $query->where('language', $language);
        }

        return $query->get();
    }

    public function findByIdForUser(int $id, int $userId): ?CoverLetter
    {
        return CoverLetter::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): CoverLetter
    {
        return CoverLetter::create($data);
    }

    public function update(CoverLetter $coverLetter, array $data): CoverLetter
    {
        $coverLetter->fill($data);
        $coverLetter->save();

        return $coverLetter;
    }

    public function delete(CoverLetter $coverLetter): bool
    {
        return $coverLetter->delete();
    }

    public function findById(int $id): ?CoverLetter
    {
        return CoverLetter::find($id);
    }

    public function findActiveTemplate(int $id): ?CoverLetterTemplate
    {
        return CoverLetterTemplate::where('id', $id)
            ->where('is_active', true)
            ->first();
    }

    public function getActiveTemplates(): Collection
    {
        return CoverLetterTemplate::where('is_active', true)->get();
    }
}
