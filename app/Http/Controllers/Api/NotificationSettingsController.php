<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class NotificationSettingsController extends BaseApiController
{
    public function show(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'notify_contact_email' => (bool) $user->notify_contact_email,
            'notify_contact_push' => (bool) $user->notify_contact_push,
        ], __('messages.notification_settings_retrieved'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'notify_contact_email' => ['sometimes', 'boolean'],
            'notify_contact_push' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $user->fill($validated);
        $user->save();

        return $this->successResponse([
            'notify_contact_email' => (bool) $user->notify_contact_email,
            'notify_contact_push' => (bool) $user->notify_contact_push,
        ], __('messages.notification_settings_saved'));
    }
}
