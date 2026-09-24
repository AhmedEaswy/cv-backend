<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesTemplatePreview
{
    public function getPreviewUrlAttribute(): ?string
    {
        return $this->resolvePreviewPath($this->preview);
    }

    public function getPreviewArUrlAttribute(): ?string
    {
        return $this->resolvePreviewPath($this->preview_ar);
    }

    /**
     * Prefer the Arabic preview when the app locale is ar and one exists.
     */
    public function resolvedPreviewUrl(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ar' && $this->preview_ar) {
            return $this->preview_ar_url;
        }

        return $this->preview_url;
    }

    private function resolvePreviewPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}
