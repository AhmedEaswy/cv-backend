<?php

namespace App\Support;

/**
 * Build clickable hrefs for CV contact fields and project URLs.
 */
class CvLink
{
    public static function href(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:'.$value;
        }

        // Phone numbers: mostly digits with common separators.
        if (preg_match('/^[\d\s+().\-]+$/', $value)) {
            $digits = preg_replace('/\D+/', '', $value);
            if (strlen((string) $digits) >= 7) {
                return 'tel:'.preg_replace('/[^\d+]/', '', $value);
            }
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        if (preg_match('#^www\.#i', $value) || preg_match('#^[a-z0-9][a-z0-9.-]*\.[a-z]{2,}(/|$)#i', $value)) {
            return 'https://'.$value;
        }

        return null;
    }

    /**
     * Render a contact/project value as an anchor when linkable, else plain escaped text.
     */
    public static function tag(?string $value, string $class = ''): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $escaped = e($value);
        $classAttr = $class !== '' ? ' class="'.e($class).'"' : '';
        $href = self::href($value);

        if ($href === null) {
            return '<span'.$classAttr.'>'.$escaped.'</span>';
        }

        $extra = str_starts_with($href, 'http')
            ? ' target="_blank" rel="noopener noreferrer"'
            : '';

        return '<a href="'.e($href).'"'.$classAttr.$extra.'>'.$escaped.'</a>';
    }
}
