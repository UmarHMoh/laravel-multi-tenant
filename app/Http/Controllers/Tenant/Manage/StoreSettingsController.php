<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StoreSettingsController extends Controller
{
    public function edit()
    {
        $tenantId = tenant('id');

        $data = tenancy()->central(function () use ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);
            $settings = $this->tenantData($tenant);

            return [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'is_active' => $tenant->is_active,
                ],
                'settings' => [
                    'store_name' => $settings['store_name'] ?? $tenant->name,
                    'store_email' => $settings['store_email'] ?? $tenant->email,
                    'store_description' => $settings['store_description'] ?? '',
                    'store_phone' => $settings['store_phone'] ?? '',
                    'store_currency' => $settings['store_currency'] ?? 'TTD',
                    'business_address' => $settings['business_address'] ?? '',

                    'delivery_fee' => (float) ($settings['delivery_fee'] ?? 0),
                    'cash_on_delivery_enabled' => (bool) ($settings['cash_on_delivery_enabled'] ?? false),

                    'instagram_url' => $settings['instagram_url'] ?? '',
                    'facebook_url' => $settings['facebook_url'] ?? '',
                    'tiktok_url' => $settings['tiktok_url'] ?? '',
                    'whatsapp_number' => $settings['whatsapp_number'] ?? '',
                ],
            ];
        });

        return Inertia::render('tenant/settings/Store', $data);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_email' => ['required', 'email', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:2000'],
            'store_phone' => ['nullable', 'string', 'max:255'],
            'store_currency' => ['required', 'string', 'size:3'],
            'business_address' => ['nullable', 'string', 'max:2000'],

            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'cash_on_delivery_enabled' => ['boolean'],

            'instagram_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'tiktok_url' => ['nullable', 'string', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'],
        ]);

        $tenantId = tenant('id');

        tenancy()->central(function () use ($tenantId, $validated) {
            $tenant = Tenant::findOrFail($tenantId);
            $existing = $this->tenantData($tenant);

            $newSettings = array_merge($existing, [
                'store_name' => $validated['store_name'],
                'store_email' => $validated['store_email'],
                'store_description' => $validated['store_description'] ?? '',
                'store_phone' => $validated['store_phone'] ?? '',
                'store_currency' => strtoupper($validated['store_currency']),
                'business_address' => $validated['business_address'] ?? '',

                'delivery_fee' => (float) $validated['delivery_fee'],
                'cash_on_delivery_enabled' => (bool) ($validated['cash_on_delivery_enabled'] ?? false),

                'instagram_url' => $validated['instagram_url'] ?? '',
                'facebook_url' => $validated['facebook_url'] ?? '',
                'tiktok_url' => $validated['tiktok_url'] ?? '',
                'whatsapp_number' => $validated['whatsapp_number'] ?? '',
            ]);

            DB::connection(config('tenancy.database.central_connection') ?: config('database.default'))
                ->table('tenants')
                ->where('id', $tenantId)
                ->update([
                    'name' => $validated['store_name'],
                    'email' => $validated['store_email'],
                    'data' => json_encode($newSettings),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('manage.store-settings.edit')
            ->with('success', 'Store settings saved successfully.');
    }

    private function tenantData(Tenant $tenant): array
    {
        $raw = $tenant->getRawOriginal('data');

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        $data = $tenant->data ?? [];

        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
