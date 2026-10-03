<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * JSON map of locale => string stored in the database.
 *
 * @implements CastsAttributes<array<string, string>, array<string, string>|string|null>
 */
class TranslatedText implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $this->normalizeMap($value);
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $this->normalizeMap($decoded) : [];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return json_encode(['en' => $value], JSON_UNESCAPED_UNICODE) ?: null;
        }

        if (! is_array($value)) {
            return null;
        }

        $map = $this->normalizeMap($value);

        return $map === [] ? null : (json_encode($map, JSON_UNESCAPED_UNICODE) ?: null);
    }

    /**
     * @param  array<string, mixed>  $map
     * @return array<string, string>
     */
    private function normalizeMap(array $map): array
    {
        $out = [];
        foreach ($map as $locale => $text) {
            if (! is_string($locale) || ! is_string($text)) {
                continue;
            }
            $locale = strtolower(substr($locale, 0, 2));
            $text = trim($text);
            if ($text === '') {
                continue;
            }
            $out[$locale] = $text;
        }

        return $out;
    }
}
