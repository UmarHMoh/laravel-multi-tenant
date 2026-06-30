<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $role = strtolower((string) ($user->role ?? ''));

        if (! in_array($role, ['admin', 'owner', 'staff'], true)) {
            abort(403, 'This area is only available to store administrators.');
        }

        return $next($request);
    }
}
