<?php

namespace App\Http\Controllers\Api;

use App\Models\UserAiSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiSettingController extends BaseApiController
{
    public function show(Request $request)
    {
        $setting = UserAiSetting::query()->firstOrNew([
            'user_id' => $request->user()->id,
        ], [
            'provider' => 'openai',
            'model' => array_key_first(UserAiSetting::providerModels()['openai'] ?? []) ?: 'gpt-4o-mini',
        ]);

        return $this->successResponse(
            $this->format($setting),
            __('messages.ai_settings_retrieved')
        );
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', Rule::in(['openai', 'openrouter', 'custom'])],
            'api_key' => ['nullable', 'string', 'max:2000'],
            'model' => ['nullable', 'string', 'max:255'],
            'custom_url' => ['nullable', 'string', 'max:500'],
            'clear_api_key' => ['sometimes', 'boolean'],
        ]);

        $provider = $validated['provider'];
        $customUrl = filled($validated['custom_url'] ?? null) ? trim((string) $validated['custom_url']) : null;
        $model = filled($validated['model'] ?? null) ? trim((string) $validated['model']) : null;

        if ($provider === 'custom' && $customUrl && ! filter_var($customUrl, FILTER_VALIDATE_URL)) {
            return $this->errorResponse(__('messages.ai_settings_invalid_url'), 422);
        }

        if ($provider !== 'custom') {
            $models = UserAiSetting::providerModels()[$provider] ?? [];
            if ($model && ! array_key_exists($model, $models)) {
                $model = array_key_first($models) ?: $model;
            }
            if (! $model) {
                $model = array_key_first($models) ?: null;
            }
            $customUrl = null;
        }

        $setting = UserAiSetting::query()->firstOrNew([
            'user_id' => $request->user()->id,
        ]);

        $setting->provider = $provider;
        $setting->model = $model;
        $setting->custom_url = $customUrl;
        $setting->base_url = UserAiSetting::resolveBaseUrl($provider, $customUrl);

        if (! empty($validated['clear_api_key'])) {
            $setting->api_key = null;
        } elseif (array_key_exists('api_key', $validated) && filled($validated['api_key'])) {
            $setting->api_key = $validated['api_key'];
        }

        $setting->save();

        return $this->successResponse(
            $this->format($setting->fresh()),
            __('messages.ai_settings_saved')
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function format(UserAiSetting $setting): array
    {
        return [
            'provider' => $setting->provider ?: 'openai',
            'model' => $setting->model,
            'custom_url' => $setting->custom_url,
            'base_url' => $setting->base_url,
            'has_api_key' => $setting->exists && $setting->hasApiKey(),
            'providers' => [
                ['value' => 'openai', 'label' => 'OpenAI'],
                ['value' => 'openrouter', 'label' => 'OpenRouter'],
                ['value' => 'custom', 'label' => 'Custom'],
            ],
            'provider_models' => UserAiSetting::providerModels(),
        ];
    }
}
