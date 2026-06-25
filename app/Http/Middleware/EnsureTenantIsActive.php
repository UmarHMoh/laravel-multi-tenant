<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return $next($request);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant || $tenant->is_active) {
            return $next($request);
        }

        $tenantData = $tenant->data ?? [];

        return Inertia::render('tenant/StoreUnavailable', [
            'storeName' => $tenantData['store_name'] ?? $tenant->name,
            'storeEmail' => $tenantData['store_email'] ?? $tenant->email,
        ])->toResponse($request)->setStatusCode(503);
    }
}
