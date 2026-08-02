<?php

namespace App\Filament\Support;

class ImagePlaceholder
{
    public static function url(): string
    {
        return asset('images/placeholder.png');
    }

    /**
     * @return array<string, string>
     */
    public static function imgAttributes(): array
    {
        $url = self::url();

        return [
            'onerror' => "this.onerror=null;this.src='{$url}';",
        ];
    }
}
