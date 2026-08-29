<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect already-authenticated users away from guest-only pages
 * (login, register, forgot/reset password).
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (Auth::guard($guard)->check()) {
            return redirect()->intended(route('portal.dashboard'));
        }

        return $next($request);
    }
}
