<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManualScoreIntent
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if ($request->input('intent') !== 'manual_essay_scoring') {
            return back()->withErrors([
                'manual_score' => 'Intent manual score tidak valid.',
            ]);
        }

        return $next($request);
    }
}
