<?php

namespace App\Http\Middleware;

use App\Support\Security\Permissions;
use Closure;
use Illuminate\Http\Request;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (! Permissions::check($request->user(), $permission)) {
            abort(403, 'Forbidden: this action requires the "'.$permission.'" permission.');
        }

        return $next($request);
    }
}
