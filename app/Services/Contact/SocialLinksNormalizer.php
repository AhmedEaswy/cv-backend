<?php

namespace App\Services\Contact;

class SocialLinksNormalizer
{
    /**
     * @return list<string>
     */
    public static function platforms(): array
    {
        return [
            'linkedin',
            'github',
            'x',
            'instagram',
            'youtube',
            'facebook',
            'tiktok',
            'snapchat',
            'calendly',
            'behance',
            'dribbble',
            'medium',
            'whatsapp',
            'telegram',
            'website',
            'custom',
        ];
    }

    /**
     * @param  mixed  $input
     * @return list<array{platform: string, url: string, label?: string}>
     */
    public function normalize(mixed $input): array
    {
        if (! is_array($input) || $input === []) {
            return [];
        }

        if ($this->isListShape($input)) {
            return $this->normalizeList($input);
        }

        return $this->normalizeKeyedMap($input);
    }

    /**
     * @param  list<mixed>  $items
     * @return list<array{platform: string, url: string, label?: string}>
     */
    private function normalizeList(array $items): array
    {
        $out = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $platform = strtolower(trim((string) ($item['platform'] ?? 'custom')));
            if (! in_array($platform, self::platforms(), true)) {
                $platform = 'custom';
            }

            $url = trim((string) ($item['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $entry = [
                'platform' => $platform,
                'url' => $url,
            ];

            $label = trim((string) ($item['label'] ?? ''));
            if ($label !== '') {
                $entry['label'] = $label;
            } elseif ($platform === 'custom' && ! empty($item['label'])) {
                $entry['label'] = (string) $item['label'];
            }

            $out[] = $entry;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $map
     * @return list<array{platform: string, url: string, label?: string}>
     */
    private function normalizeKeyedMap(array $map): array
    {
        $out = [];

        foreach ($map as $key => $value) {
            if (is_string($value) && $value !== '') {
                $platform = strtolower((string) $key);
                if (! in_array($platform, self::platforms(), true)) {
                    $platform = 'custom';
                    $out[] = [
                        'platform' => $platform,
                        'url' => $value,
                        'label' => (string) $key,
                    ];
                } else {
                    $out[] = [
                        'platform' => $platform,
                        'url' => $value,
                    ];
                }

                continue;
            }

            if (is_array($value)) {
                $url = trim((string) ($value['url'] ?? ''));
                if ($url === '') {
                    continue;
                }

                $platform = strtolower(trim((string) ($value['platform'] ?? $key)));
                if (! in_array($platform, self::platforms(), true)) {
                    $platform = 'custom';
                }

                $entry = [
                    'platform' => $platform,
                    'url' => $url,
                ];

                $label = trim((string) ($value['label'] ?? ''));
                if ($label !== '') {
                    $entry['label'] = $label;
                } elseif ($platform === 'custom') {
                    $entry['label'] = is_string($key) ? $key : 'Link';
                }

                $out[] = $entry;
            }
        }

        return $out;
    }

    private function isListShape(array $input): bool
    {
        if ($input === []) {
            return true;
        }

        return array_keys($input) === range(0, count($input) - 1);
    }
}
