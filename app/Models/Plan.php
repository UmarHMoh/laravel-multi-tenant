<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'commission_rate',
        'transaction_fee_percent',
        'transaction_fee_fixed',
        'max_products',
        'custom_domain_enabled',
        'api_payment_enabled',
        'allow_multiple_builder_pages',
        'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'transaction_fee_percent' => 'decimal:4',
        'transaction_fee_fixed' => 'decimal:2',
        'custom_domain_enabled' => 'boolean',
        'api_payment_enabled' => 'boolean',
        'allow_multiple_builder_pages' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

}
