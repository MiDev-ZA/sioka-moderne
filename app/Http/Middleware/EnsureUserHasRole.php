<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage : ->middleware('role:editor,journalist')
     * L'admin a toujours accès (accès complet).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'admin' && ! in_array($user->role, $roles, true))) {
            abort(403, 'Vous n\'avez pas accès à cette section.');
        }

        return $next($request);
    }
}