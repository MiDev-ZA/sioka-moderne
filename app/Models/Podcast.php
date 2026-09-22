<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Podcast extends Model
{
    use HasFactory;

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