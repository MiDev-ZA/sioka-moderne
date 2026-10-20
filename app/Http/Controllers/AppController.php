<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Fil;
use App\Models\FlashInfo;
use App\Models\Podcast;
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
            ->take(5)
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

        // Récupétation des podcasts
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
            $allVideos = Cache::remember('youtube_all_public_videos', 900, function () {
                return app(YoutubeService::class)->listPublic(100);
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
 

    // Methode pour affiche la liste des podcasts modal
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

    public function dashboard()
    {
        return view('dashboard.vue-ensemble');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function actualites()
    {
        return view('dashboard.actualites', [
            'articles' => Article::with('category')->latest()->paginate(10),
            'categories' => Category::orderBy('name')->get(),
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
        return view('dashboard.emissions');
    }

    public function podcasts()
    {
       return view('dashboard.podcasts', [
        'podcasts' => Podcast::with('user')->latest()->paginate(10),
        ]);
    }

    public function abonnees_newsletter()
    {
        return view('dashboard.abonnees-newsletter');
    }

    public function lequipe()
    {
        return view('dashboard.lequipe');
    }

    public function profil()
    {
        return view('dashboard.profil');
    }
}