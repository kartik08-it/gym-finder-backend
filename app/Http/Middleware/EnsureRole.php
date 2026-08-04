<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }
        if (! in_array($user->role->value ?? $user->role, $roles, true)) {
            abort(403, 'Forbidden — insufficient role.');
        }
        return $next($request);
    }
}
