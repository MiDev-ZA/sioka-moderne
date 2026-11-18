<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'name', 'advertiser', 'slot', 'image', 'image_alt', 'url',
        'starts_at', 'ends_at', 'is_active', 'impressions', 'clicks',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'is_active' => 'boolean',
    ];

    public function scopeDisplayable($query, string $slot)
    {
        return $query->where('slot', $slot)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', today()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', today()));
    }
}