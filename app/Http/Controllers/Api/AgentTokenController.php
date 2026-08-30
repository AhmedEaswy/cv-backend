<?php

namespace App\Http\Controllers\Api;

use App\Support\AgentAbilities;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentTokenController extends BaseApiController
{
    public function index(Request $request)
    {
        $tokens = $request->user()
            ->tokens()
            ->where('name', 'like', AgentAbilities::TOKEN_NAME_PREFIX.'%')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
            ]);

        return $this->successResponse($tokens, __('agent.tokens_retrieved'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string', Rule::in(AgentAbilities::all())],
        ]);

        $label = AgentAbilities::TOKEN_NAME_PREFIX.$data['name'];
        $abilities = $data['abilities'] ?? AgentAbilities::all();

        $newToken = $request->user()->createToken($label, $abilities);

        return $this->successResponse([
            'id' => $newToken->accessToken->id,
            'name' => $newToken->accessToken->name,
            'abilities' => $newToken->accessToken->abilities,
            'token' => $newToken->plainTextToken,
        ], __('agent.token_created'), 201);
    }

    public function destroy(Request $request, string $id)
    {
        $token = $request->user()
            ->tokens()
            ->where('name', 'like', AgentAbilities::TOKEN_NAME_PREFIX.'%')
            ->whereKey($id)
            ->first();

        if (! $token) {
            return $this->errorResponse(__('agent.token_not_found'), 404);
        }

        $token->delete();

        return $this->successResponse(null, __('agent.token_revoked'));
    }
}
