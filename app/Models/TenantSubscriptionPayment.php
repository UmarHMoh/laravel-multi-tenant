<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscriptionPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_subscription_id',
        'plan_id',

        'amount',
        'currency',
        'status',
        'source',

        'paid_at',
        'previous_renews_at',
        'new_renews_at',

        'payment_method',
        'provider',
        'provider_transaction_id',

        'payer_name',
        'payer_email',
        'card_brand',
        'card_last4',
        'receipt_url',

        'recorded_by',
        'reference',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
        'previous_renews_at' => 'date',
        'new_renews_at' => 'date',
        'metadata' => 'array',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'tenant_subscription_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
