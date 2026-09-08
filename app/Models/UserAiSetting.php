<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAiSetting extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'api_key',
        'model',
        'custom_url',
        'base_url',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasApiKey(): bool
    {
        return filled($this->api_key);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function providerModels(): array
    {
        return [
            'openai' => [
                'gpt-4o-mini' => 'GPT-4o mini',
                'gpt-4.1-mini' => 'GPT-4.1 mini',
                'gpt-4.1' => 'GPT-4.1',
            ],
            'openrouter' => [
                'openai/gpt-4o-mini' => 'OpenAI GPT-4o mini',
                'openai/gpt-4.1-mini' => 'OpenAI GPT-4.1 mini',
                'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function providerBaseUrlMap(): array
    {
        return [
            'openai' => 'https://api.openai.com/v1',
            'openrouter' => 'https://openrouter.ai/api/v1',
        ];
    }

    public static function resolveBaseUrl(string $provider, ?string $customUrl = null): string
    {
        if ($provider === 'custom') {
            return (string) $customUrl;
        }

        return self::providerBaseUrlMap()[$provider] ?? 'https://api.openai.com/v1';
    }
}
