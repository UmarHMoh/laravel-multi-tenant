<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutBankOption extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }
}
