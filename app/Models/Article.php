<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'image',
        'image_caption',
        'image_alt',
        'summary',
        'content',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Durée de lecture estimée en minutes (minimum 1), à 200 mots par minute.
     * Utilisation : {{ $article->reading_time }} min
     */
    protected function readingTime(): Attribute
    {
        return Attribute::get(function () {
            // Les balises deviennent des espaces (évite de coller les mots entre blocs),
            // puis les entités HTML (&nbsp;, &eacute;, &rsquo;...) sont décodées
            $text = preg_replace('/<[^>]*>/', ' ', (string) $this->content);
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Mots Unicode : lettres accentuées, chiffres, apostrophes et traits d'union gérés
            $words = preg_match_all('/[\p{L}\p{N}]+(?:[\'’-][\p{L}\p{N}]+)*/u', $text);

            return max(1, (int) ceil($words / 200));
        });
    }
}