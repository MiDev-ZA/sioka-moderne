<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Podcast extends Model
{
    use HasFactory;

    /**
     * Image affichée quand aucune couverture n'a été envoyée (dans public/images).
     */
    public const DEFAULT_IMAGE = 'images/SIOKA-LogoOfficiel_2.png';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'soundcloud_url',
        'image',
        'image_caption',
        'image_alt',
        'is_published',
        'user_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * L'auteur du podcast.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * URL de la couverture : image envoyée, sinon le logo SIOKA par défaut.
     */
    public function getCoverUrlAttribute(): string
    {
        return !empty($this->image)
            ? asset('storage/' . $this->image)
            : asset(self::DEFAULT_IMAGE);
    }

    /**
     * Génère un slug unique à partir du titre si aucun slug n'est fourni.
     */
    public static function generateUniqueSlug(string $title, ?string $desiredSlug = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($desiredSlug ?: $title);
        $slug = $base;
        $i = 1;

        $query = fn ($s) => static::where('slug', $s)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        while ($query($slug)) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}