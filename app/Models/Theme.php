<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'settings',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'published_at' => 'datetime',
    ];

    public function pages()
    {
        return $this->hasMany(ThemePage::class);
    }

    public function homepage()
    {
        return $this->hasOne(ThemePage::class)->where('type', 'home');
    }

    public static function active()
    {
        return static::query()->where('is_active', true)->latest('id')->first();
    }
}
