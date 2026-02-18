<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOperator
{
    /**
     * Ensure authenticated user has operator role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_OPERATOR) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
