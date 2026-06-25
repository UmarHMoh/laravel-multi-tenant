<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'renews_at',
        'trial_ends_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'renews_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

}
