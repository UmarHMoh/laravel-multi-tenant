<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlatformTransaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_order_id',
        'provider',
        'provider_transaction_id',
        'recorded_at',
        'source_event',
        'idempotency_key',
        'currency',
        'gross_amount',
        'processor_fee',
        'platform_fee_amount',
        'platform_fee_fixed',
        'platform_fee_percent',
        'total_tenant_fee_amount',
        'tenant_transaction_fee_fixed',
        'tenant_transaction_fee_percent',
        'processor_fee_fixed',
        'processor_fee_percent',
        'net_amount',
        'commission_rate',
        'commission_amount',
        'tenant_payout_amount',
        'payment_status',
        'payout_status',
        'paid_at',
        'payout_marked_at',
        'metadata',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'gross_amount' => 'decimal:2',
        'processor_fee' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'platform_fee_fixed' => 'decimal:2',
        'platform_fee_percent' => 'decimal:4',
        'total_tenant_fee_amount' => 'decimal:2',
        'tenant_transaction_fee_fixed' => 'decimal:2',
        'tenant_transaction_fee_percent' => 'decimal:4',
        'processor_fee_fixed' => 'decimal:2',
        'processor_fee_percent' => 'decimal:4',
        'net_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'tenant_payout_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payout_marked_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function payouts(): BelongsToMany
    {
        return $this->belongsToMany(
            TenantPayout::class,
            'tenant_payout_items',
            'platform_transaction_id',
            'tenant_payout_id'
        )->withTimestamps();
    }

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

}
