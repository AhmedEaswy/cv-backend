<?php

namespace App\Services;

use App\Models\CoverLetter;

class CoverLetterDataMapper
{
    private const TEXT_FIELDS = [
        'firstName',
        'lastName',
        'email',
        'phone',
        'address',
        'jobTitle',
        'companyName',
        'recipientName',
        'recipientTitle',
        'recipientCompany',
        'subject',
        'body',
        'closing',
    ];

    public function mapUserDataToCoverLetter(array $userData): array
    {
        $userData = $this->normalizeJsonArray($userData);
        $info = [];

        foreach (self::TEXT_FIELDS as $field) {
            if (! array_key_exists($field, $userData)) {
                continue;
            }

            $info[$field] = $this->normalizeTextField($userData[$field]);
        }

        $mapped = [];

        if ($info) {
            $mapped['info'] = $info;
        }

        if (isset($userData['experiences'])) {
            $mapped['experiences'] = $this->normalizeJsonArray($userData['experiences']);
        }

        return $mapped;
    }

    public function formatCoverLetterResponse(CoverLetter $coverLetter): array
    {
        $userData = [];

        $info = $this->normalizeJsonArray($coverLetter->info);
        foreach ($info as $key => $value) {
            if (in_array($key, self::TEXT_FIELDS, true)) {
                $userData[$key] = $this->normalizeTextField($value);
            } else {
                $userData[$key] = $value;
            }
        }

        $experiences = $this->normalizeJsonArray($coverLetter->experiences);
        if ($experiences !== []) {
            $userData['experiences'] = $experiences;
        }

        return [
            'id' => $coverLetter->id,
            'user_id' => $coverLetter->user_id,
            'name' => $coverLetter->name,
            'language' => $coverLetter->language,
            'is_public' => $coverLetter->is_public,
            'sections_order' => $coverLetter->sections_order,
            'cover_letter_template_id' => $coverLetter->cover_letter_template_id,
            'user_data' => $userData,
            'created_at' => $coverLetter->created_at?->toIso8601String(),
            'updated_at' => $coverLetter->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeJsonArray(mixed $value): array
    {
        if ($value === null || $value === []) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizeJsonArray($decoded);
            }

            return [];
        }

        if (is_object($value)) {
            return $this->normalizeJsonArray((array) $value);
        }

        return is_array($value) ? $value : [];
    }

    private function normalizeTextField(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $parts = array_filter(array_map(
                fn ($part) => trim((string) $part),
                $value
            ));

            return $parts === [] ? null : implode("\n\n", $parts);
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return $value;
        }

        return null;
    }
}
