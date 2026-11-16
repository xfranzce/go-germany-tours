<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Blocks admin areas the signed-in user's role may not use (Editors: packages, posts, pages, media, categories/tags). */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('admin');
        abort_if($user && ! $user->canAccessRoute($request->route()?->getName()), 403, 'Your role does not have access to this area.');

        return $next($request);
    }
}
