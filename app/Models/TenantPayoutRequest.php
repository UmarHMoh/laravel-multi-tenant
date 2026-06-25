<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPayoutRequest extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_payout_account_id',
        'tenant_payout_ledger_entry_id',
        'tenant_payout_batch_id',
        'amount',
        'currency',
        'status',
        'reference',
        'tenant_notes',
        'admin_notes',
        'rejection_reason',
        'requested_at',
        'approved_at',
        'rejected_at',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function payoutAccount()
    {
        return $this->belongsTo(TenantPayoutAccount::class, 'tenant_payout_account_id');
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(TenantPayoutLedgerEntry::class, 'tenant_payout_ledger_entry_id');
    }

    public function batch()
    {
        return $this->belongsTo(TenantPayoutBatch::class, 'tenant_payout_batch_id');
    }
}
