<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_id',
        'handle',
        'title',
        'type',
        'template',
        'draft_config',
        'published_config',
        'published_at',
        'product_id',
    ];

    protected $casts = [
        'draft_config' => 'array',
        'published_config' => 'array',
        'published_at' => 'datetime',
    ];

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function activeConfig(): array
    {
        return $this->published_config ?: $this->draft_config ?: ['sections' => []];
    }
}
