<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $user = Auth::user();

        // Use the isAdmin() method from the User model for reliable checking
        if (!$user->isAdmin()) {
            Auth::logout();
            $typeDisplay = $user->type ? ($user->type instanceof UserType ? $user->type->value : $user->type) : 'not set';
            return redirect()->route('filament.admin.auth.login')->withErrors([
                'email' => 'Access denied. Admin privileges required. Current type: ' . $typeDisplay,
            ]);
        }

        return $next($request);
    }
}
