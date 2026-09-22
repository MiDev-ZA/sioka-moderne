<?php

namespace App\Http\Controllers;

use App\Services\YoutubeService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WebTvController extends Controller
{
    protected const CACHE_KEY = 'youtube_videos_all';

    public function index(Request $request)
    {
        try {
            $allVideos = Cache::remember(self::CACHE_KEY, 300, function () {
                return app(YoutubeService::class)->listAll();
            });
        } catch (\Exception $e) {
            return redirect()
                ->route('youtube.auth')
                ->with('info', 'Connexion à la chaîne YouTube requise.');
        }

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = collect($allVideos)->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $videos = new LengthAwarePaginator(
            $items,
            count($allVideos),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('dashboard.webtv', compact('videos'));
    }

    public function store(Request $request, YoutubeService $youtube)
    {
        $request->validate([
            'video'          => 'required|file|mimetypes:video/mp4,video/quicktime|max:512000',
            'title'          => 'required|string|max:100',
            'description'    => 'nullable|string|max:5000',
            'privacyStatus'  => 'required|in:public,unlisted,private',
        ]);

        $path = $request->file('video')->store('temp');

        try {
            $youtube->upload(
                storage_path('app/' . $path),
                $request->title,
                $request->description ?? '',
                $request->privacyStatus
            );
        } catch (\Exception $e) {
            Storage::delete($path);
            return back()->withInput()->with('error', 'Échec de la publication : ' . $e->getMessage());
        }

        Storage::delete($path);
        Cache::forget(self::CACHE_KEY);

        return redirect()->route('webtv')->with('success', 'Vidéo publiée sur YouTube.');
    }

    public function update(Request $request, string $videoId, YoutubeService $youtube)
    {
        $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:5000',
        ]);

        try {
            $youtube->updateVideo($videoId, $request->title, $request->description ?? '');
        } catch (\Exception $e) {
            return back()->with('error', 'Échec de la modification : ' . $e->getMessage());
        }

        Cache::forget(self::CACHE_KEY);

        return back()->with('success', 'Vidéo modifiée.');
    }

    public function destroy(string $videoId, YoutubeService $youtube)
    {
        try {
            $youtube->delete($videoId);
        } catch (\Exception $e) {
            return back()->with('error', 'Échec de la suppression : ' . $e->getMessage());
        }

        Cache::forget(self::CACHE_KEY);

        return back()->with('success', 'Vidéo supprimée.');
    }

    protected function webtvSection(): array
    {
        $liveStatus = Cache::get('youtube_live_status');

        $videos = Cache::remember('youtube_public_videos', 900, function () {
            try {
                return app(YoutubeService::class)->listPublic(20);
            } catch (\Exception $e) {
                return [];
            }
        });

        return [
            'webtvIsLive'  => (bool) $liveStatus,
            'webtvLive'    => $liveStatus,
            'webtvHero'    => $liveStatus ? null : ($videos[0] ?? null),
            'webtvReplays' => $liveStatus ? array_slice($videos, 0, 5) : array_slice($videos, 1, 5),
            'webtvArchive' => $liveStatus ? array_slice($videos, 5) : array_slice($videos, 6),
        ];
    }

    public function show(string $id)
    {
        $video = $this->findVideoById($id);

        abort_if(!$video, 404);

        $relatedVideos = $this->getRecentVideos(6, except: $id);

        return view('partials.show', [
            'video' => $video,
            'relatedVideos' => $relatedVideos,
        ]);
    }

    /**
     * Liste complète des vidéos publiques, mise en cache séparément de
     * webtvSection() (qui ne garde que les 20 dernières pour l'accueil) afin
     * de pouvoir retrouver une vidéo plus ancienne par son id.
     */
    protected function getPublicVideos(): array
    {
        return Cache::remember('youtube_public_videos_full', 900, function () {
            try {
                return app(YoutubeService::class)->listPublic(null);
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    protected function findVideoById(string $id): ?array
    {
        return collect($this->getPublicVideos())->firstWhere('id', $id);
    }

    protected function getRecentVideos(int $limit, ?string $except = null): array
    {
        return collect($this->getPublicVideos())
            ->reject(fn ($video) => $video['id'] === $except)
            ->take($limit)
            ->values()
            ->all();
    }
}