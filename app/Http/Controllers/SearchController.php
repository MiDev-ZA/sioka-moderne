<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Podcast;
use App\Services\YoutubeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    private const MIN_CHARS = 2;
    private const MAX_TERMS = 5;
    private const LIMIT = 8;

    // Même clé et même durée que WebTvController::getPublicVideos() :
    // le cache est partagé, et vidé par WebTvController::clearVideoCaches().
    private const VIDEOS_CACHE_KEY = 'youtube_public_videos_full';
    private const VIDEOS_CACHE_TTL = 900;

    /**
     * Recherche en direct (modale du header).
     * Réponse : { articles: [...], videos: [...] }
     */
    public function index(Request $request): JsonResponse
    {
        $query = trim(mb_substr((string) $request->query('q', ''), 0, 100));

        if (mb_strlen($query) < self::MIN_CHARS) {
            return response()->json(['articles' => [], 'videos' => [], 'podcasts' => []]);
        }

        $terms = array_slice(preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY), 0, self::MAX_TERMS);

        return response()->json([
            'articles' => $this->searchArticles($query, $terms),
            'videos'   => $this->searchVideos($terms),
            'podcasts' => $this->searchPodcasts($terms),
        ]);
    }

    // ── Articles ──────────────────────────────────────────────

    private function searchArticles(string $query, array $terms): array
    {
        $like = fn (string $s) => '%' . addcslashes($s, '\\%_') . '%';

        // Chaque mot doit apparaître dans le titre, le résumé ou le contenu.
        $articles = Article::query()
            ->select(['id', 'category_id', 'title', 'slug', 'summary', 'content', 'image', 'created_at'])
            ->where('is_published', true)
            ->where(function ($q) use ($terms, $like) {
                foreach ($terms as $term) {
                    $q->where(function ($w) use ($term, $like) {
                        $w->where('title', 'like', $like($term))
                            ->orWhere('summary', 'like', $like($term))
                            ->orWhere('content', 'like', $like($term));
                    });
                }
            })
            ->orderByRaw('title LIKE ? DESC', [$like($query)]) // titres correspondant à la phrase d'abord
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        $categories = Category::whereIn('id', $articles->pluck('category_id')->filter()->unique())
            ->pluck('name', 'id');

        return $articles->map(fn ($a) => [
            'title'    => $a->title,
            'url'      => route('articles.show', ['article' => $a->slug]),
            'summary'  => $this->excerpt($a->summary, $a->content, $terms),
            'category' => $categories[$a->category_id] ?? null,
            'date'     => $a->created_at?->translatedFormat('j F Y'),
            'image'    => $this->imageUrl($a->image),
        ])->all();
    }

    // ── Vidéos archives (YouTube) ─────────────────────────────

    /**
     * Cherche dans la liste des vidéos publiques déjà en cache (aucun appel API
     * supplémentaire quand le cache est chaud). Insensible à la casse et aux accents.
     * Une panne YouTube ne doit jamais casser la recherche d'articles.
     */
    private function searchVideos(array $terms): array
    {
        $videos = $this->publicVideos();
        if (!$videos) {
            return [];
        }

        $needles = array_map([$this, 'fold'], $terms);

        $matches = [];
        foreach ($videos as $video) {
            $title = $this->fold($video['title'] ?? '');
            $haystack = $title . ' ' . $this->fold($video['description'] ?? '');

            foreach ($needles as $needle) {
                if (!str_contains($haystack, $needle)) {
                    continue 2;
                }
            }

            $inTitle = true;
            foreach ($needles as $needle) {
                if (!str_contains($title, $needle)) {
                    $inTitle = false;
                    break;
                }
            }

            $matches[] = ['rank' => $inTitle ? 0 : 1, 'video' => $video];
        }

        // Titres d'abord ; à rang égal, l'ordre de la liste (les plus récentes en premier).
        usort($matches, fn ($a, $b) => $a['rank'] <=> $b['rank']);
        $matches = array_slice($matches, 0, self::LIMIT);

        $labels = $this->categoryLabels(array_column(array_column($matches, 'video'), 'category'));

        return array_map(function ($m) use ($terms, $labels) {
            $v = $m['video'];

            return [
                'title'    => $v['title'] ?? '',
                'url'      => route('webtv.show', ['id' => $v['id']]),
                'summary'  => $this->excerpt('', $v['description'] ?? '', $terms),
                'category' => $labels[$v['category'] ?? ''] ?? null,
                'date'     => $this->formatDate($v['publishedAt'] ?? null),
                'image'    => ($v['thumbnail'] ?? '') !== '' ? $v['thumbnail'] : null,
            ];
        }, $matches);
    }

    private function publicVideos(): array
    {
        $videos = Cache::get(self::VIDEOS_CACHE_KEY);

        if ($videos === null) {
            try {
                $videos = app(YoutubeService::class)->listPublic(null);
                Cache::put(self::VIDEOS_CACHE_KEY, $videos, self::VIDEOS_CACHE_TTL);
            } catch (\Throwable $e) {
                report($e);

                return [];
            }
        }

        return $videos;
    }

    /** slug de catégorie YouTube (tag « cat:xxx ») → libellé affichable. */
    private function categoryLabels(array $slugs): array
    {
        $slugs = array_values(array_unique(array_filter($slugs)));
        if (!$slugs) {
            return [];
        }

        $names = Category::whereIn('slug', $slugs)->pluck('name', 'slug');

        $labels = [];
        foreach ($slugs as $slug) {
            $labels[$slug] = $names[$slug] ?? Str::of($slug)->replace('-', ' ')->ucfirst()->toString();
        }

        return $labels;
    }

    private function formatDate(?string $iso): ?string
    {
        if (!$iso) {
            return null;
        }

        try {
            return Carbon::parse($iso)->translatedFormat('j F Y');
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ── Podcasts ──────────────────────────────────────────────

    /**
     * Même règle que la section publique : seuls les podcasts lisibles
     * (avec une URL SoundCloud) sont proposés. Recherche sur le titre.
     */
    private function searchPodcasts(array $terms): array
    {
        $podcasts = Podcast::query()
            ->with('user')
            ->whereNotNull('soundcloud_url')
            ->where('soundcloud_url', '!=', '')
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where('title', 'like', '%' . addcslashes($term, '\\%_') . '%');
                }
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return $podcasts->map(fn ($p) => [
            'title'    => $p->title,
            // Pas de page dédiée à un podcast : on renvoie vers la section Podcasts de l'accueil.
            'url'      => route('home') . '#podcasts',
            'summary'  => '',
            'category' => $p->user->name ?? 'SIOKA',
            'date'     => $p->created_at?->translatedFormat('j F Y'),
            'image'    => $this->imageUrl($p->image),
            'soundcloud_url' => $p->soundcloud_url,
        ])->all();
    }

    // ── Utilitaires ───────────────────────────────────────────

    /** Minuscules sans accents, pour comparer « Économie » et « economie ». */
    private function fold(string $s): string
    {
        return Str::lower(Str::ascii($s));
    }

    /** Extrait : le résumé s'il contient un mot cherché, sinon un passage du contenu autour du mot. */
    private function excerpt(?string $summary, ?string $content, array $terms): string
    {
        $summary = trim(strip_tags((string) $summary));
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $content), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        if ($summary !== '') {
            foreach ($terms as $term) {
                if (mb_stripos($summary, $term) !== false) {
                    return Str::limit($summary, 160);
                }
            }
        }

        foreach ($terms as $term) {
            $pos = mb_stripos($text, $term);
            if ($pos !== false) {
                $start = max(0, $pos - 60);
                $snippet = trim(mb_substr($text, $start, 160));

                return ($start > 0 ? '…' : '') . $snippet . (mb_strlen($text) > $start + 160 ? '…' : '');
            }
        }

        return Str::limit($summary !== '' ? $summary : $text, 160);
    }

    private function imageUrl(?string $image): ?string
    {
        if (!$image) {
            return null;
        }

        return preg_match('#^https?://#i', $image) ? $image : asset('storage/' . ltrim($image, '/'));
    }
}