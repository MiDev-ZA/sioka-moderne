<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Equipe;
use App\Models\Fil;
use App\Models\FlashInfo;
use App\Models\NewsletterSubscriber;
use App\Models\Podcast;
use App\Models\Programme;
use App\Services\GoogleAnalyticsAudience;
use App\Services\GoogleAnalyticsService;
use App\Services\YoutubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AppController extends Controller
{
    public function home()
    {
        $featuredArticles = Article::with(['category', 'user'])
            ->where('is_published', true)
            ->whereHas('category', fn ($q) => $q->where('slug', 'a-la-une'))
            ->latest()
            ->take(4)
            ->get();

        $recentArticles = Article::with(['category', 'user'])
            ->where('is_published', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = $recentArticles
            ->pluck('category')
            ->unique('id')
            ->sortBy(function ($category) {
                return $category->slug === 'a-la-une' ? 0 : 1;
            })
            ->values();

        // Récupération des fils d'infos
        $fils = Fil::with('category')
            ->where('is_active', true)
            ->latest()
            ->take(10)
            ->get();

        // Récupération des podcasts
        $podcasts = Podcast::where('is_published', true)
            ->latest()
            ->take(4)
            ->with('user')
            ->get();

        // WebTV — vidéos YouTube (direct en cours si disponible, sinon dernière publication)
        $liveStatus = Cache::get('youtube_live_status');

        $webtvVideos = Cache::get('youtube_public_videos');
        if ($webtvVideos === null) {
            try {
                $webtvVideos = app(YoutubeService::class)->listPublic(7);
                if (!empty($webtvVideos)) {
                    Cache::put('youtube_public_videos', $webtvVideos, now()->addMinutes(15));
                }
            } catch (\Exception $e) {
                $webtvVideos = [];
            }
        }

        $webtvIsLive  = (bool) $liveStatus;
        $webtvLive    = $liveStatus;
        $webtvHero    = $webtvIsLive ? null : ($webtvVideos[0] ?? null);
        $webtvRecent  = $webtvIsLive ? array_slice($webtvVideos, 0, 6) : array_slice($webtvVideos, 1, 6);

        return view('home', compact(
            'featuredArticles',
            'recentArticles',
            'categories',
            'fils',
            'podcasts',
            'webtvIsLive',
            'webtvLive',
            'webtvHero',
            'webtvRecent'
        ));
    }

    public function webtvIndex(Request $request)
    {
        try {
            $allVideos = Cache::remember('youtube_all_public_videos_v2', 900, function () {
                return app(YoutubeService::class)->listPublic(null);
            });
        } catch (\Exception $e) {
            $allVideos = [];
        }

        $filtered = collect($allVideos);

        // Catégories disponibles (calculées avant filtrage pour que les chips restent toutes visibles)
        $categories = $filtered->pluck('category')->filter()->unique()->sort()->values()->all();

        // Filtre par catégorie
        $category = (string) $request->query('category', '');
        if ($category !== '') {
            $filtered = $filtered->where('category', $category);
        }

        // Recherche par début de mot (dès la 1re lettre), sans accents ni casse.
        // 1-2 caractères : titre seul (sinon presque tout correspond) ; 3+ : titre + description.
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $tokens = preg_split('/[^a-z0-9]+/', Str::lower(Str::ascii($q)), -1, PREG_SPLIT_NO_EMPTY);
            $titleOnly = mb_strlen($q) < 3;

            $filtered = $filtered->filter(function ($video) use ($tokens, $titleOnly) {
                $text = ($video['title'] ?? '') . ($titleOnly ? '' : ' ' . ($video['description'] ?? ''));
                $words = preg_split('/[^a-z0-9]+/', Str::lower(Str::ascii($text)), -1, PREG_SPLIT_NO_EMPTY);

                foreach ($tokens as $token) {
                    $found = false;
                    foreach ($words as $word) {
                        if (str_starts_with($word, $token)) { $found = true; break; }
                    }
                    if (!$found) return false;
                }
                return true;
            });
        }

        // Tri : "popular" = plus vues, sinon plus récentes
        $filtered = $request->query('sort') === 'popular'
            ? $filtered->sortByDesc('views')
            : $filtered->sortByDesc('publishedAt');
        $filtered = $filtered->values();

        $perPage = 12;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $items = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $videos = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $filtered->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Requête AJAX (filtrage en direct) : on renvoie uniquement les fragments à remplacer
        if ($request->wantsJson()) {
            return response()->json([
                'count'   => \Illuminate\Support\Number::format($videos->total()) . ' vidéo(s)',
                'active'  => view('partials.videos-active')->render(),
                'results' => view('partials.videos-results', compact('videos'))->render(),
            ])->header('Vary', 'Accept');
        }

        return response(view('partials/videos-all', compact('videos', 'categories')))->header('Vary', 'Accept');
    }

    // Méthode pour afficher la liste des podcasts dans la modale
    public function podcastsLibrary()
    {
        $podcasts = Podcast::where('is_published', true)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $groups = $podcasts->groupBy(function ($podcast) {
            return $podcast->created_at->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        });

        $currentWeekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $lastWeekStart = now()->subWeek()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $weekLabels = $groups->keys()->mapWithKeys(function ($key) use ($currentWeekStart, $lastWeekStart) {
            if ($key === $currentWeekStart) {
                $label = 'Cette semaine';
            } elseif ($key === $lastWeekStart) {
                $label = 'Semaine dernière';
            } else {
                $start = Carbon::parse($key);
                $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
                $label = 'Semaine du ' . $start->translatedFormat('d')
                    . ' au ' . $end->translatedFormat('d F Y');
            }

            return [$key => $label];
        });

        return view('partials.podcasts-library-list', compact('groups', 'weekLabels'));
    }

    public function loadMoreNews(Request $request)
    {
        $offset = (int) $request->query('offset', 6);
        $perPage = 6;

        $articles = Article::with(['category', 'user'])
            ->where('is_published', true)
            ->latest()
            ->skip($offset)
            ->take($perPage + 1) // +1 pour savoir s'il en reste après
            ->get();

        $hasMore = $articles->count() > $perPage;
        $articles = $articles->take($perPage);

        $html = view('partials.article-card-list', ['articles' => $articles])->render();

        return response()->json([
            'html' => $html,
            'count' => $articles->count(),
            'hasMore' => $hasMore,
        ]);
    }

    public function newsIndex()
    {
        $articles = Article::with(['category', 'user'])
            ->where('is_published', true)
            ->latest()
            ->paginate(12);

        $categories = Category::orderBy('name')->get()
            ->sortBy(function ($category) {
                return $category->slug === 'a-la-une' ? 0 : 1;
            })
            ->values();

        return view('news-index', compact('articles', 'categories'));
    }

    public function categoryShow(Category $category)
    {
        $articles = Article::with(['category', 'user'])
            ->where('is_published', true)
            ->whereHas('category', fn ($q) => $q->where('slug', $category->slug))
            ->latest()
            ->paginate(12);

        $categories = Category::orderBy('name')->get()
            ->sortBy(function ($cat) {
                return $cat->slug === 'a-la-une' ? 0 : 1;
            })
            ->values();

        return view('news-index', compact('articles', 'categories', 'category'));
    }

    // Page publique : grille des émissions de la semaine
    public function emissionsPublic()
    {
        $programmes = Programme::orderBy('start')->get()->groupBy('day');

        // Jour courant et émission en cours : toujours à l'heure de Madagascar (UTC+3),
        // quel que soit le fuseau configuré dans config/app.php
        $now        = now('Indian/Antananarivo');
        $jourActuel = Programme::JOURS[$now->dayOfWeekIso - 1];
        $heure      = $now->format('H:i:s');

        $enDirectId = ($programmes[$jourActuel] ?? collect())
            ->first(fn ($p) => $p->start <= $heure && $p->end > $heure)?->id;

        return view('sections.emissions_publique', [
            'jours'      => Programme::JOURS,
            'programmes' => $programmes,
            'jourActuel' => $jourActuel,
            'enDirectId' => $enDirectId,
            'nowMinutes' => $now->hour * 60 + $now->minute + $now->second / 60,
        ]);
    }

    // Page publique : organigramme de l'équipe (uniquement les membres cochés « Afficher dans l'organigramme »)
    public function equipePublic()
    {
        return view('sections.equipe', [
            'members' => Equipe::where('org', true)->orderBy('id')->get()->map->toMember()->values(),
        ]);
    }

    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Adresse e-mail ou mot de passe incorrect.');
    }

    public function dashboard(GoogleAnalyticsService $analytics, GoogleAnalyticsAudience $audience)
    {
        $ga = $analytics->dashboard();

        if ($ga['available']) {
            $ga['audience'] = $audience->fetch();
        }

        return view('dashboard.vue-ensemble', [
            'ga' => $ga,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Liste des articles du dashboard : filtres côté serveur (recherche, catégorie, statut)
    public function actualites(Request $request)
    {
        // Entrées de filtrage : texte uniquement (refuse ?q[]=x), sans caractères de contrôle, longueur bornée
        $clean = function (string $key, int $max = 100) use ($request): string {
            $raw = $request->query($key);

            return is_string($raw)
                ? mb_substr(trim((string) preg_replace('/[\x00-\x1F\x7F]/u', '', $raw)), 0, $max)
                : '';
        };

        $q          = $clean('q');
        $categoryId = $clean('category', 10);
        $status     = $clean('status', 10);

        // Valeurs autorisées uniquement
        $categoryId = ctype_digit($categoryId) ? $categoryId : '';
        $status     = in_array($status, ['published', 'draft'], true) ? $status : '';

        $query = Article::with(['category', 'user'])
            // Recherche : chaque mot saisi doit apparaître dans le titre, le résumé, le slug ou le contenu
            ->when($q !== '', function ($query) use ($q) {
                $tokens = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY);

                foreach (array_slice($tokens, 0, 6) as $token) {
                    // Neutralise les jokers LIKE (% et _) saisis par l'utilisateur ; la valeur reste un paramètre lié
                    $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $token) . '%';

                    $query->where(function ($w) use ($term) {
                        $w->whereRaw("title LIKE ? ESCAPE '!'", [$term])
                          ->orWhereRaw("summary LIKE ? ESCAPE '!'", [$term])
                          ->orWhereRaw("slug LIKE ? ESCAPE '!'", [$term])
                          ->orWhereRaw("content LIKE ? ESCAPE '!'", [$term]);
                    });
                }
            })
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', (int) $categoryId))
            ->when($status === 'published', fn ($query) => $query->where('is_published', true))
            ->when($status === 'draft', fn ($query) => $query->where('is_published', false))
            ->latest()
            ->orderByDesc('id');

        $perPage  = 10;
        $articles = (clone $query)->paginate($perPage)->withQueryString();

        // Après une suppression, la dernière page peut devenir vide : retour à la dernière page valide
        if ($articles->isEmpty() && $articles->currentPage() > 1) {
            $articles = $query->paginate($perPage, ['*'], 'page', $articles->lastPage())->withQueryString();
        }

        return view('dashboard.actualites', [
            'articles'   => $articles,
            'categories' => Category::orderBy('name')->get(),
            'filters'    => [
                'q'        => $q,
                'category' => $categoryId,
                'status'   => $status,
            ],
            'hasFilters' => $q !== '' || $categoryId !== '' || $status !== '',
        ]);
    }

    public function flash_infos()
    {
        return view('dashboard.flash-infos', [
            'flashInfos' => FlashInfo::with(['user', 'category'])->latest()->paginate(10),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function fil_infos()
    {
        return view('dashboard.fil-en-continu', [
            'fils' => Fil::with(['user', 'category'])->latest()->paginate(10),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function emissions()
    {
        return view('dashboard.emissions', [
            'programmes' => Programme::orderBy('start')->get(),
        ]);
    }

    public function podcasts()
    {
        return view('dashboard.podcasts', [
            'podcasts' => Podcast::with('user')->latest()->paginate(10),
        ]);
    }

    // Liste des abonnés à la newsletter (recherche instantanée + pagination)
    public function abonnees_newsletter(Request $request)
    {
        // Entrée de recherche : texte uniquement (refuse ?q[]=x), sans caractères de contrôle, 100 caractères max
        $raw = $request->query('q');
        $q   = is_string($raw)
            ? mb_substr(trim((string) preg_replace('/[\x00-\x1F\x7F]/u', '', $raw)), 0, 100)
            : '';

        $perPage = 25;

        $query = NewsletterSubscriber::query()
            ->when($q !== '', function ($query) use ($q) {
                // Neutralise les jokers LIKE (% et _) saisis par l'utilisateur ; la valeur reste un paramètre lié
                $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q);
                $query->whereRaw("email LIKE ? ESCAPE '!'", ['%' . $term . '%']);
            })
            ->latest()
            ->orderByDesc('id');

        $abonnes = (clone $query)->paginate($perPage)->withQueryString();

        // Après une suppression, la dernière page peut devenir vide : retour à la dernière page valide
        if ($abonnes->isEmpty() && $abonnes->currentPage() > 1) {
            $abonnes = $query->paginate($perPage, ['*'], 'page', $abonnes->lastPage())->withQueryString();
        }

        // Requête AJAX (recherche en direct, suppression) : uniquement le fragment à remplacer
        if ($request->wantsJson()) {
            return response()->json([
                'html'  => view('dashboard.partials.abonnees-results', compact('abonnes', 'q'))->render(),
                'total' => NewsletterSubscriber::count(),
                'page'  => $abonnes->currentPage(),
            ])->header('Vary', 'Accept')
              ->header('Cache-Control', 'no-store')
              ->header('X-Content-Type-Options', 'nosniff');
        }

        return response(view('dashboard.abonnees-newsletter', [
            'abonnes' => $abonnes,
            'total'   => NewsletterSubscriber::count(),
            'q'       => $q,
        ]))->header('Vary', 'Accept');
    }

    public function lequipe()
    {
        return view('dashboard.lequipe', [
            'members' => Equipe::orderBy('id')->get()->map->toMember()->values(),
        ]);
    }

    public function profil()
    {
        return view('dashboard.profil');
    }
}