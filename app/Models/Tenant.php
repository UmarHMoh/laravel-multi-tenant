<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'is_active',
        'data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'data' => 'array',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'is_active',
            'data',
        ];
    }

    public function payoutAccounts(): HasMany
    {
        return $this->hasMany(TenantPayoutAccount::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(TenantPayout::class);
    }

    public function platformTransactions(): HasMany
    {
        return $this->hasMany(PlatformTransaction::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class)
            ->whereIn('status', ['active', 'past_due'])
            ->latestOfMany();
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(TenantSubscriptionPayment::class);
    }

    public function latestSubscriptionPayment(): HasOne
    {
        return $this->hasOne(TenantSubscriptionPayment::class)->latestOfMany();
    }
}
