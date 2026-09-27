<?php

namespace App\Http\Controllers\Api;

use App\Models\DevicePushToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DevicePushTokenController extends BaseApiController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'string', Rule::in(['ios', 'android'])],
            'app_version' => ['sometimes', 'nullable', 'string', 'max:40'],
        ]);

        $device = DevicePushToken::query()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'],
                'app_version' => $validated['app_version'] ?? null,
                'last_used_at' => now(),
            ]
        );

        return $this->successResponse([
            'id' => $device->id,
            'platform' => $device->platform,
        ], __('messages.device_token_registered'), 201);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        DevicePushToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $validated['token'])
            ->delete();

        return $this->successResponse(null, __('messages.device_token_removed'));
    }
}
