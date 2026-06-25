<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'name',
        'config_type',
        'provider',
        'environment',
        'country_code',
        'currency',
        'account_number',
        'api_key',
        'fee_structure',
        'auth_type',
        'base_url',
        'checkout_endpoint',
        'verify_endpoint',
        'refund_endpoint',
        'payout_endpoint',
        'token_endpoint',
        'public_key',
        'secret_key',
        'private_key',
        'webhook_secret',
        'merchant_id',
        'account_id',
        'username',
        'password',
        'bearer_token',
        'processor_fee_percent',
        'processor_fee_fixed',
        'headers_template',
        'request_template',
        'response_mapping',
        'is_active',
        'enabled_at',
        'metadata',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'public_key' => 'encrypted',
        'secret_key' => 'encrypted',
        'private_key' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'password' => 'encrypted',
        'bearer_token' => 'encrypted',

        'processor_fee_percent' => 'decimal:4',
        'processor_fee_fixed' => 'decimal:2',

        'headers_template' => 'array',
        'request_template' => 'array',
        'response_mapping' => 'array',
        'metadata' => 'array',

        'is_active' => 'boolean',
        'enabled_at' => 'datetime',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public static function active()
    {
        return static::where('is_active', true)->latest('enabled_at')->latest()->first();
    }

    public function activate(): void
    {
        static::query()->update([
            'is_active' => false,
            'enabled_at' => null,
        ]);

        $this->forceFill([
            'is_active' => true,
            'enabled_at' => now(),
        ])->save();
    }

    public function displayName(): string
    {
        return $this->name ?: strtoupper((string) $this->provider);
    }
}
