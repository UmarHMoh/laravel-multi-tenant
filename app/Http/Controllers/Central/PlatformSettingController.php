<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlatformSettingController extends Controller
{
    public function edit()
    {
        return Inertia::render('central/settings/Edit', [
            'settings' => PlatformSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'platform_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'subscription_currency' => ['required', 'string', 'size:3'],
            'renewal_grace_days' => ['required', 'integer', 'min:0', 'max:365'],
            'auto_deactivate_overdue_tenants' => ['boolean'],
            'login_heading' => ['required', 'string', 'max:255'],
            'login_subheading' => ['nullable', 'string', 'max:1000'],
        ]);

        PlatformSetting::current()->update([
            'platform_name' => $validated['platform_name'],
            'owner_email' => $validated['owner_email'] ?? null,
            'subscription_currency' => strtoupper($validated['subscription_currency']),
            'renewal_grace_days' => $validated['renewal_grace_days'],
            'auto_deactivate_overdue_tenants' => (bool) ($validated['auto_deactivate_overdue_tenants'] ?? false),
            'login_heading' => $validated['login_heading'],
            'login_subheading' => $validated['login_subheading'] ?? null,
        ]);

        return back()->with('success', 'Platform settings updated successfully.');
    }
}
