<?php

namespace App\Mcp\Tools\Concerns;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

trait RequiresAbility
{
    abstract protected function requiredAbility(): string;

    public function shouldRegister(Request $request): bool
    {
        return $this->userCan($request, $this->requiredAbility());
    }

    protected function denyUnlessAble(Request $request): ?Response
    {
        if ($this->userCan($request, $this->requiredAbility())) {
            return null;
        }

        return Response::error('This tool requires a token with the '.$this->requiredAbility().' ability.');
    }
}
