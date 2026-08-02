<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CvPhotoService
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * If user_data.photo is base64/data-URI, store it and replace with relative path.
     * Existing URLs/paths are left as-is.
     */
    public function processUserDataPhoto(array $userData): array
    {
        if (! array_key_exists('photo', $userData) || $userData['photo'] === null || $userData['photo'] === '') {
            return $userData;
        }

        $photo = $userData['photo'];

        if (! is_string($photo)) {
            throw new InvalidArgumentException('Photo must be a string.');
        }

        if ($this->isHttpUrl($photo) || $this->looksLikeStoragePath($photo)) {
            return $userData;
        }

        $userData['photo'] = $this->storeFromBase64($photo);

        return $userData;
    }

    /**
     * Validate a photo value; return error message or null if valid/empty.
     */
    public function validationError(?string $photo): ?string
    {
        if ($photo === null || $photo === '') {
            return null;
        }

        if ($this->isHttpUrl($photo) || $this->looksLikeStoragePath($photo)) {
            return null;
        }

        try {
            $this->decodeBase64($photo);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }

        return null;
    }

    public function storeFromBase64(string $input): string
    {
        [$binary, $mime] = $this->decodeBase64($input);
        $extension = self::ALLOWED_MIMES[$mime];
        $relativePath = 'cv-photos/' . Str::uuid()->toString() . '.' . $extension;

        Storage::disk('public')->put($relativePath, $binary);

        return $relativePath;
    }

    /**
     * Resolve a stored path or absolute URL to a public URL for Blade/PDF.
     */
    public function urlFor(?string $photo): ?string
    {
        if ($photo === null || $photo === '') {
            return null;
        }

        if ($this->isHttpUrl($photo)) {
            return $photo;
        }

        if (str_starts_with($photo, '/storage/')) {
            return url($photo);
        }

        return Storage::disk('public')->url($photo);
    }

    /**
     * @return array{0: string, 1: string} [binary, mime]
     */
    private function decodeBase64(string $input): array
    {
        $mime = null;
        $payload = $input;

        if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/s', $input, $matches)) {
            $mime = strtolower($matches[1]);
            $payload = $matches[2];
        }

        $binary = base64_decode($payload, true);

        if ($binary === false || $binary === '') {
            throw new InvalidArgumentException('Photo must be valid base64, a data URI, a URL, or a storage path.');
        }

        if (strlen($binary) > self::MAX_BYTES) {
            throw new InvalidArgumentException('Photo must be 2MB or smaller.');
        }

        if ($mime === null) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($binary) ?: '';
        }

        if (! isset(self::ALLOWED_MIMES[$mime])) {
            throw new InvalidArgumentException('Photo must be a JPEG, PNG, or WebP image.');
        }

        return [$binary, $mime];
    }

    private function isHttpUrl(string $value): bool
    {
        return (bool) preg_match('#^https?://#i', $value);
    }

    private function looksLikeStoragePath(string $value): bool
    {
        return str_starts_with($value, 'cv-photos/')
            || str_starts_with($value, 'storage/')
            || str_starts_with($value, '/storage/');
    }
}
