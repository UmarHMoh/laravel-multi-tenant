<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPayoutLedgerEntry extends Model
{
    protected $fillable = [
        'tenant_id',
        'platform_transaction_id',
        'entry_type',
        'source',
        'status',
        'gross_amount',
        'processor_fee_amount',
        'platform_fee_amount',
        'tenant_net_amount',
        'currency',
        'reference',
        'metadata',
        'available_at',
        'paid_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'processor_fee_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'tenant_net_amount' => 'decimal:2',
        'metadata' => 'array',
        'available_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public function platformTransaction()
    {
        return $this->belongsTo(PlatformTransaction::class);
    }
}
