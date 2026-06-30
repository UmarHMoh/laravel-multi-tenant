<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'source_page',
        'ip_address',
        'user_agent',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function markRead(): void
    {
        if (! $this->is_read) {
            $this->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }
    }
}
