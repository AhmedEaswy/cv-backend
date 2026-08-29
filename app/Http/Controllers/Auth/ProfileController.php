<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('portal.settings.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $newEmail = strtolower($data['email']);
        $emailChanged = $newEmail !== strtolower($user->email);

        $user->fill([
            'name' => trim($data['name']),
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $newEmail,
            'phone' => $data['phone'] ?? null,
        ]);

        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null]);
        }

        $user->save();

        $message = $emailChanged
            ? __('messages.profile_updated_email_pending')
            : __('messages.profile_updated');

        return back()->with('status', $message);
    }
}
