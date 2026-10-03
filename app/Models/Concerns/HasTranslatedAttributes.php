<?php

namespace App\Models\Concerns;

use App\Support\AppLocale;

trait HasTranslatedAttributes
{
    public function translation(string $attribute, ?string $locale = null): ?string
    {
        $map = $this->getAttribute($attribute);
        if (! is_array($map) || $map === []) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();
        if (isset($map[$locale]) && $map[$locale] !== '') {
            return $map[$locale];
        }

        $fallback = AppLocale::fallback();
        if (isset($map[$fallback]) && $map[$fallback] !== '') {
            return $map[$fallback];
        }

        foreach ($map as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
