<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * Check if the user is a consumer who hasn't changed their default password.
     * If so, redirect them to the password change page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for authenticated users
        if (! $user) {
            return $next($request);
        }

        // Only apply to consumers
        if ($user->role?->slug !== 'consumer') {
            return $next($request);
        }

        // Check if password has never been changed (null = first login)
        if (is_null($user->password_changed_at)) {
            // Allow access to password change routes to avoid redirect loop
            if ($request->routeIs('password.force-change', 'password.force-change.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
