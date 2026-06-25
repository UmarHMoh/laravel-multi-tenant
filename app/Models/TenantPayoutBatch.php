<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPayoutBatch extends Model
{
    protected $fillable = [
        'batch_number',
        'status',
        'request_count',
        'total_amount',
        'currency',
        'notes',
        'approved_at',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public function requests()
    {
        return $this->hasMany(TenantPayoutRequest::class, 'tenant_payout_batch_id');
    }
}
