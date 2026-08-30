<?php

namespace App\Mcp\Tools\Concerns;

use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

trait InteractsWithToolPayload
{
    public function name(): string
    {
        $base = class_basename(static::class);

        if (str_ends_with($base, 'Tool')) {
            $base = substr($base, 0, -4);
        }

        return \Illuminate\Support\Str::kebab($base);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeUserData(Request $request, string $key = 'user_data'): array
    {
        $raw = $request->get($key);

        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                $key => 'Must be a JSON object.',
            ]);
        }

        return $decoded;
    }

    protected function ok(mixed $result, string $message = ''): Response
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'result' => $result,
        ]);
    }

    protected function fail(string $message): Response
    {
        return Response::error($message);
    }

    protected function userCan(Request $request, string $ability): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        $token = $user->currentAccessToken();

        if (! $token || $token->abilities === ['*'] || $token->abilities === []) {
            return true;
        }

        return $user->tokenCan($ability);
    }
}
