<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayoutAccount extends Model
{
    protected $fillable = [
        'tenant_id',
        'type',
        'account_holder_name',
        'bank_name',
        'bank_account_number',
        'branch_transit_number',
        'bank_account_type',
        'wipay_account_email',
        'status',
        'notes',
        'review_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $hidden = [
        'bank_account_number',
        'branch_transit_number',
        'wipay_account_email',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
