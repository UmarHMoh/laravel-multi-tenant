<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('central') && ! $request->is('central/*')) {
            return $next($request);
        }

        if ($request->is('central/login') || $request->is('central/logout')) {
            return $next($request);
        }

        if ($request->session()->get('central_admin_authenticated') === true) {
            return $next($request);
        }

        return redirect('/central/login')->with('error', 'Please log in to access the central admin area.');
    }
}
