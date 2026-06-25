<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'platform_name',
        'owner_email',
        'subscription_currency',
        'renewal_grace_days',
        'auto_deactivate_overdue_tenants',
        'login_heading',
        'login_subheading',
        'metadata',
    ];

    protected $casts = [
        'renewal_grace_days' => 'integer',
        'auto_deactivate_overdue_tenants' => 'boolean',
        'metadata' => 'array',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'platform_name' => 'Central Admin',
            'owner_email' => null,
            'subscription_currency' => 'TTD',
            'renewal_grace_days' => 0,
            'auto_deactivate_overdue_tenants' => true,
            'login_heading' => 'Central Admin Login',
            'login_subheading' => 'Manage tenants, plans, payments, renewals, transactions, payouts, and platform settings.',
        ]);
    }
}
