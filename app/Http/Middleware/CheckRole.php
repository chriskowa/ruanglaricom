<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Handle pipe-separated roles
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (str_contains($role, '|')) {
                $allowedRoles = array_merge($allowedRoles, explode('|', $role));
            } else {
                $allowedRoles[] = $role;
            }
        }

        if (! in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
