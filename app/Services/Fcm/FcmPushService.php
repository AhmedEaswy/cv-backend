<?php

namespace App\Services\Fcm;

use App\Models\DevicePushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToToken(DevicePushToken $device, string $title, string $body, array $data = []): bool
    {
        $credentialsPath = env('FIREBASE_CREDENTIALS');

        if (! is_string($credentialsPath) || $credentialsPath === '' || ! is_readable($credentialsPath)) {
            Log::info('FCM skipped: FIREBASE_CREDENTIALS not configured', [
                'user_id' => $device->user_id,
                'platform' => $device->platform,
            ]);

            return false;
        }

        try {
            $credentials = json_decode((string) file_get_contents($credentialsPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::warning('FCM credentials invalid', ['error' => $e->getMessage()]);

            return false;
        }

        $accessToken = $this->fetchAccessToken($credentials);
        if ($accessToken === null) {
            return false;
        }

        $projectId = $credentials['project_id'] ?? null;
        if (! is_string($projectId) || $projectId === '') {
            Log::warning('FCM credentials missing project_id');

            return false;
        }

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $device->token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data),
                ],
            ]);

        if ($response->successful()) {
            $device->forceFill(['last_used_at' => now()])->save();

            return true;
        }

        $status = $response->status();
        $payload = $response->json();
        $errorCode = $payload['error']['details'][0]['errorCode']
            ?? $payload['error']['status']
            ?? null;

        if ($this->isInvalidTokenError($status, $errorCode)) {
            $device->delete();
        }

        Log::warning('FCM send failed', [
            'status' => $status,
            'body' => $response->body(),
            'token_id' => $device->id,
        ]);

        return false;
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function fetchAccessToken(array $credentials): ?string
    {
        if (class_exists(\Google\Client::class)) {
            try {
                $client = new \Google\Client;
                $client->setAuthConfig($credentials);
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                $token = $client->fetchAccessTokenWithAssertion();

                return is_array($token) ? ($token['access_token'] ?? null) : null;
            } catch (\Throwable $e) {
                Log::warning('FCM Google client failed', ['error' => $e->getMessage()]);
            }
        }

        Log::info('FCM skipped: install google/apiclient or set FIREBASE_CREDENTIALS for HTTP JWT flow');

        return null;
    }

    private function isInvalidTokenError(int $status, mixed $errorCode): bool
    {
        if ($status === 404) {
            return true;
        }

        if (! is_string($errorCode)) {
            return false;
        }

        return in_array(strtoupper($errorCode), ['UNREGISTERED', 'INVALID_ARGUMENT'], true);
    }
}
