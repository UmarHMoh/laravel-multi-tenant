<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        $this->ensureDefaultSavedConfiguration();

        $settings = PaymentSetting::query()
            ->latest()
            ->get()
            ->map(fn (PaymentSetting $setting) => $this->payload($setting))
            ->values();

        return Inertia::render('central/payment-settings/Edit', [
            'settings' => $settings,
            'activeSetting' => PaymentSetting::active() ? $this->payload(PaymentSetting::active()) : null,
            'authTypes' => [
                'none',
                'api_key',
                'bearer_token',
                'basic',
                'oauth2_client_credentials',
                'custom_headers',
            ],
            'configTypes' => [
                'saved',
                'custom',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $this->validateSetting($request);

        $setting = isset($validated['id'])
            ? PaymentSetting::findOrFail($validated['id'])
            : new PaymentSetting();

        $secretFields = [
            'api_key',
            'public_key',
            'secret_key',
            'private_key',
            'webhook_secret',
            'password',
            'bearer_token',
        ];

        foreach ($secretFields as $field) {
            if (! array_key_exists($field, $validated) || blank($validated[$field])) {
                unset($validated[$field]);
            }
        }

        foreach (['headers_template', 'request_template', 'response_mapping', 'metadata'] as $jsonField) {
            if (isset($validated[$jsonField]) && is_string($validated[$jsonField])) {
                $validated[$jsonField] = $this->decodeJsonField($validated[$jsonField], $jsonField);
            }
        }

        $shouldActivate = (bool) ($validated['is_active'] ?? false);

        $validated['is_active'] = false;
        $validated['name'] = $validated['name'] ?: strtoupper($validated['provider']);
        $validated['config_type'] = $validated['config_type'] ?: 'saved';
        $validated['fee_structure'] = $validated['fee_structure'] ?: 'merchant_absorb';
        $validated['auth_type'] = $validated['auth_type'] ?: 'none';

        $setting->fill($validated);
        $setting->save();

        if ($shouldActivate) {
            $setting->activate();
        }

        return redirect()
            ->route('central.payment-settings.edit')
            ->with('success', $shouldActivate
                ? 'Payment configuration saved and activated successfully.'
                : 'Payment configuration saved successfully.');
    }

    private function validateSetting(Request $request): array
    {
        return $request->validate([
            'id' => ['nullable', 'integer', 'exists:payment_settings,id'],

            'name' => ['nullable', 'string', 'max:255'],
            'config_type' => ['required', 'string', 'in:saved,custom'],
            'provider' => ['required', 'string', 'max:100'],
            'environment' => ['required', 'string', 'in:sandbox,live'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'currency' => ['required', 'string', 'max:10'],
            'account_number' => ['nullable', 'string', 'max:255'],

            'fee_structure' => ['nullable', 'string', 'in:customer_pay,merchant_absorb,split'],
            'auth_type' => ['nullable', 'string', 'max:100'],
            'base_url' => ['nullable', 'string', 'max:1000'],
            'checkout_endpoint' => ['nullable', 'string', 'max:1000'],
            'verify_endpoint' => ['nullable', 'string', 'max:1000'],
            'refund_endpoint' => ['nullable', 'string', 'max:1000'],
            'payout_endpoint' => ['nullable', 'string', 'max:1000'],
            'token_endpoint' => ['nullable', 'string', 'max:1000'],

            'api_key' => ['nullable', 'string', 'max:5000'],
            'public_key' => ['nullable', 'string', 'max:5000'],
            'secret_key' => ['nullable', 'string', 'max:5000'],
            'private_key' => ['nullable', 'string', 'max:5000'],
            'webhook_secret' => ['nullable', 'string', 'max:5000'],
            'merchant_id' => ['nullable', 'string', 'max:255'],
            'account_id' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:5000'],
            'bearer_token' => ['nullable', 'string', 'max:5000'],

            'processor_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'processor_fee_fixed' => ['required', 'numeric', 'min:0'],

            'headers_template' => ['nullable'],
            'request_template' => ['nullable'],
            'response_mapping' => ['nullable'],
            'metadata' => ['nullable'],

            'is_active' => ['boolean'],
        ]);
    }

    private function decodeJsonField(mixed $value, string $field): ?array
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => 'This field must be valid JSON.',
            ]);
        }

        return $decoded;
    }

    private function payload(PaymentSetting $setting): array
    {
        return [
            'id' => $setting->id,
            'name' => $setting->name,
            'config_type' => $setting->config_type,
            'provider' => $setting->provider,
            'environment' => $setting->environment,
            'country_code' => $setting->country_code,
            'currency' => $setting->currency,
            'account_number' => $setting->account_number,
            'fee_structure' => $setting->fee_structure,
            'auth_type' => $setting->auth_type,
            'base_url' => $setting->base_url,
            'checkout_endpoint' => $setting->checkout_endpoint,
            'verify_endpoint' => $setting->verify_endpoint,
            'refund_endpoint' => $setting->refund_endpoint,
            'payout_endpoint' => $setting->payout_endpoint,
            'token_endpoint' => $setting->token_endpoint,

            'merchant_id' => $setting->merchant_id,
            'account_id' => $setting->account_id,
            'username' => $setting->username,

            'processor_fee_percent' => $setting->processor_fee_percent,
            'processor_fee_fixed' => $setting->processor_fee_fixed,

            'headers_template' => $setting->headers_template,
            'request_template' => $setting->request_template,
            'response_mapping' => $setting->response_mapping,
            'metadata' => $setting->metadata,

            'is_active' => $setting->is_active,
            'enabled_at' => optional($setting->enabled_at)->toDateTimeString(),

            'has_api_key' => filled($setting->api_key),
            'has_public_key' => filled($setting->public_key),
            'has_secret_key' => filled($setting->secret_key),
            'has_private_key' => filled($setting->private_key),
            'has_webhook_secret' => filled($setting->webhook_secret),
            'has_password' => filled($setting->password),
            'has_bearer_token' => filled($setting->bearer_token),
        ];
    }

    private function ensureDefaultSavedConfiguration(): void
    {
        if (! PaymentSetting::query()->exists()) {
            PaymentSetting::create([
                'name' => 'WiPay Default',
                'config_type' => 'saved',
                'provider' => 'wipay',
                'environment' => 'sandbox',
                'country_code' => 'TT',
                'currency' => 'TTD',
                'account_number' => '1234567890',
                'api_key' => '123',
                'fee_structure' => 'merchant_absorb',
                'auth_type' => 'api_key',
                'processor_fee_percent' => 3.5,
                'processor_fee_fixed' => 0.30,
                'is_active' => false,
                'metadata' => [
                    'created_by' => 'system_default',
                ],
            ]);
        }

        if (! PaymentSetting::where('is_active', true)->exists()) {
            PaymentSetting::latest()->first()?->activate();
        }
    }
}
