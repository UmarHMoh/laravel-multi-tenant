<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class DashboardController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('central/Dashboard', [
            'stats' => [
                'totalTenants' => Tenant::count(),
                'activeTenants' => Tenant::where('is_active', true)->count(),
                'inactiveTenants' => Tenant::where('is_active', false)->count(),
                'totalDomains' => Domain::count(),
            ],
            'recentTenants' => $tenants->take(5)->values(),
        ]);
    }
}
