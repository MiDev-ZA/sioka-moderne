<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    protected $fillable = ['name', 'role', 'email', 'photo', 'org', 'parent_id'];

    protected $casts = [
        'org' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    /**
     * Format attendu par le JS de la page (même forme que l'ancien tableau statique).
     */
    public function toMember(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'role'   => $this->role,
            'email'  => $this->email,
            'photo'  => $this->photo_url,
            'org'    => $this->org,
            'parent' => $this->parent_id,
        ];
    }
}