<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TenantPayout extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_payout_account_id',
        'currency',
        'total_gross',
        'total_processor_fees',
        'total_net',
        'total_commission',
        'total_payout',
        'status',
        'reference',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'total_gross' => 'decimal:2',
        'total_processor_fees' => 'decimal:2',
        'total_net' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'total_payout' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(TenantPayoutAccount::class, 'tenant_payout_account_id');
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(
            PlatformTransaction::class,
            'tenant_payout_items',
            'tenant_payout_id',
            'platform_transaction_id'
        )->withTimestamps();
    }
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

}
