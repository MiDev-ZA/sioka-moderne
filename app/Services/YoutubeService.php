<?php

namespace App\Services;

use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\VideoSnippet;
use Google\Service\YouTube\VideoStatus;
use App\Models\YoutubeToken;
use Exception;

class YoutubeService
{
    protected ?Client $client = null;
    protected ?YouTube $youtube = null;
    protected ?YouTube $publicYoutube = null;

    public function __construct()
    {
        // Plus d'authentification OAuth ici : elle est désormais déclenchée
        // à la demande, uniquement par les actions qui en ont réellement
        // besoin (upload, modification, suppression, liste admin). Les
        // méthodes publiques (listPublic, getLiveBroadcast) utilisent un
        // client à clé API et n'en dépendent jamais.
    }

    /**
     * Client authentifié OAuth — pour les actions liées au compte
     * propriétaire de la chaîne (upload, édition, suppression, liste admin).
     * Lève une exception si aucun token n'existe : c'est voulu, ces actions
     * exigent une connexion via /auth/youtube.
     */
    protected function authenticatedYoutube(): YouTube
    {
        if ($this->youtube) {
            return $this->youtube;
        }

        $this->client = new Client();
        $this->client->setClientId(config('services.youtube.client_id'));
        $this->client->setClientSecret(config('services.youtube.client_secret'));
        $this->client->setScopes([YouTube::YOUTUBE]);
        $this->client->setAccessType('offline');

        $this->authenticate();

        return $this->youtube = new YouTube($this->client);
    }

    /**
     * Client public — utilise une clé API YouTube Data (config
     * services.youtube.api_key), sans OAuth. Fonctionne même si personne ne
     * s'est jamais connecté via /auth/youtube : c'est ce qui doit alimenter
     * tout ce qui est affiché sur le site public.
     */
    protected function publicYoutube(): YouTube
    {
        if ($this->publicYoutube) {
            return $this->publicYoutube;
        }

        $client = new Client();
        $client->setDeveloperKey(config('services.youtube.api_key'));

        return $this->publicYoutube = new YouTube($client);
    }

    protected function authenticate(): void
    {
        $tokenRow = YoutubeToken::latest()->first();

        if (!$tokenRow) {
            throw new Exception('Aucun token YouTube trouvé. Reconnecte-toi via /auth/youtube.');
        }

        $this->client->setAccessToken([
            'access_token'  => $tokenRow->access_token,
            'refresh_token' => $tokenRow->refresh_token,
            'expires_in'    => $tokenRow->expires_at ? now()->diffInSeconds($tokenRow->expires_at, false) : 0,
        ]);

        if ($this->client->isAccessTokenExpired()) {
            if (!$tokenRow->refresh_token) {
                throw new Exception('Token expiré et aucun refresh_token disponible. Reconnecte-toi via /auth/youtube.');
            }

            $newToken = $this->client->fetchAccessTokenWithRefreshToken($tokenRow->refresh_token);

            if (isset($newToken['error'])) {
                throw new Exception('Erreur de rafraîchissement du token: ' . $newToken['error_description']);
            }

            $tokenRow->update([
                'access_token'  => $newToken['access_token'],
                'expires_at'    => now()->addSeconds($newToken['expires_in']),
                'refresh_token' => $newToken['refresh_token'] ?? $tokenRow->refresh_token,
            ]);

            $this->client->setAccessToken($this->client->getAccessToken());
        }
    }

    /**
     * Publier une vidéo (upload résumable). $category devient un tag YouTube
     * préfixé "cat:" utilisé pour les filtres côté site public.
     */
    public function upload(string $filePath, string $title, string $description = '', string $privacyStatus = 'public', string $category = 'actualite'): string
    {
        $youtube = $this->authenticatedYoutube();
        $this->client->setDefer(true);

        $video = new Video();

        $snippet = new VideoSnippet();
        $snippet->setTitle($title);
        $snippet->setDescription($description);
        $snippet->setCategoryId('22');
        $snippet->setTags(['cat:' . $category]);
        $video->setSnippet($snippet);

        $status = new VideoStatus();
        $status->setPrivacyStatus($privacyStatus);
        $video->setStatus($status);

        $insertRequest = $youtube->videos->insert('snippet,status', $video);

        $chunkSizeBytes = 1 * 1024 * 1024;
        $media = new \Google\Http\MediaFileUpload(
            $this->client,
            $insertRequest,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($filePath));

        $status = false;
        $handle = fopen($filePath, 'rb');
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }
        fclose($handle);

        $this->client->setDefer(false);

        return $status['id'];
    }

    /**
     * Récupère UNE page de résultats (jeton YouTube brut).
     */
    public function list(int $maxResults = 10, ?string $pageToken = null): array
    {
        $youtube = $this->authenticatedYoutube();
        $channelsResponse = $youtube->channels->listChannels('contentDetails', ['mine' => true]);
        $uploadsPlaylistId = $channelsResponse['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

        $params = [
            'playlistId' => $uploadsPlaylistId,
            'maxResults' => $maxResults,
        ];
        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $playlistResponse = $youtube->playlistItems->listPlaylistItems('snippet,contentDetails', $params);

        return [
            'items'         => $playlistResponse['items'],
            'nextPageToken' => $playlistResponse['nextPageToken'] ?? null,
            'prevPageToken' => $playlistResponse['prevPageToken'] ?? null,
        ];
    }

    /**
     * Récupère TOUTES les vidéos (parcourt les pages YouTube en interne),
     * pour permettre une pagination Laravel classique (numéros de page) côté admin.
     */
    public function listAll(int $maxPerPage = 50, int $hardLimit = 500): array
    {
        $youtube = $this->authenticatedYoutube();
        $channelsResponse = $youtube->channels->listChannels('contentDetails', ['mine' => true]);
        $uploadsPlaylistId = $channelsResponse['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

        $all = [];
        $pageToken = null;

        do {
            $params = ['playlistId' => $uploadsPlaylistId, 'maxResults' => $maxPerPage];
            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = $youtube->playlistItems->listPlaylistItems('snippet,contentDetails', $params);
            $all = array_merge($all, $response['items']);
            $pageToken = $response['nextPageToken'] ?? null;

        } while ($pageToken && count($all) < $hardLimit);

        return $all;
    }

    /**
     * Récupère les vidéos publiques enrichies (durée, vues, catégorie) pour le site public.
     * Parcourt TOUTES les pages de la playlist (l'API plafonne playlistItems.list à 50
     * résultats par appel, peu importe le maxResults demandé) et découpe les appels
     * videos.list par lots de 50 IDs (plafond également imposé par l'API).
     * À mettre en cache côté contrôleur — ne jamais appeler à chaque requête.
     */
    public function listPublic(?int $maxResults = 20): array
    {
        $youtube = $this->publicYoutube();
        $channelId = config('services.youtube.channel_id');

        $channelsResponse = $youtube->channels->listChannels('contentDetails', ['id' => $channelId]);
        $uploadsPlaylistId = $channelsResponse['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

        $videoIds = [];
        $pageToken = null;

        do {
            $params = [
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => $maxResults === null
                    ? 50
                    : (min(50, $maxResults - count($videoIds)) ?: 50),
            ];
            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $playlistResponse = $youtube->playlistItems->listPlaylistItems('contentDetails', $params);

            foreach ($playlistResponse['items'] as $item) {
                $videoIds[] = $item['contentDetails']['videoId'];
            }

            $pageToken = $playlistResponse['nextPageToken'] ?? null;

        } while ($pageToken && ($maxResults === null || count($videoIds) < $maxResults));

        if (empty($videoIds)) {
            return [];
        }

        // videos.list n'accepte que 50 IDs par appel : on découpe en lots
        $allDetails = [];
        foreach (array_chunk($videoIds, 50) as $chunk) {
            $detailsResponse = $youtube->videos->listVideos('snippet,contentDetails,statistics,status', [
                'id' => implode(',', $chunk),
            ]);
            $allDetails = array_merge($allDetails, $detailsResponse['items']);
        }

        return collect($allDetails)
            ->filter(fn($video) => $video->getStatus()->getPrivacyStatus() === 'public')
            ->map(function ($video) {
            $tags = $video->getSnippet()->getTags() ?? [];
            $categoryTag = collect($tags)->first(fn($t) => str_starts_with($t, 'cat:'));
            $category = $categoryTag ? substr($categoryTag, 4) : 'actualite';

            $thumbnails = $video->getSnippet()->getThumbnails();
            $thumbnailUrl = $thumbnails->getMaxres()?->getUrl()
                ?? $thumbnails->getHigh()?->getUrl()
                ?? $thumbnails->getMedium()?->getUrl()
                ?? $thumbnails->getDefault()?->getUrl()
                ?? '';

            return [
                'id'          => $video->getId(),
                'title'       => $video->getSnippet()->getTitle(),
                'description' => $video->getSnippet()->getDescription(),
                'thumbnail'   => $thumbnailUrl,
                'publishedAt' => $video->getSnippet()->getPublishedAt(),
                'duration'    => $this->formatDuration($video->getContentDetails()->getDuration()),
                'views'       => (int) $video->getStatistics()->getViewCount(),
                'category'    => $category,
            ];
        })->values()->all();
    }

    /**
     * Vérifie si la chaîne est actuellement en direct sur YouTube.
     * Coûte 100 unités de quota — à appeler uniquement via une tâche planifiée
     * mise en cache, jamais directement à chaque chargement de page.
     */
    public function getLiveBroadcast(): ?array
    {
        $youtube = $this->publicYoutube();
        $channelId = config('services.youtube.channel_id');

        $searchResponse = $youtube->search->listSearch('snippet', [
            'channelId'  => $channelId,
            'eventType'  => 'live',
            'type'       => 'video',
            'maxResults' => 1,
        ]);

        if (empty($searchResponse['items'])) {
            return null;
        }

        $item = $searchResponse['items'][0];
        $videoId = $item->getId()->getVideoId();

        $videoResponse = $youtube->videos->listVideos('liveStreamingDetails', ['id' => $videoId]);
        $concurrentViewers = null;
        if (!empty($videoResponse['items'])) {
            $concurrentViewers = $videoResponse['items'][0]->getLiveStreamingDetails()->getConcurrentViewers();
        }

        return [
            'id'        => $videoId,
            'title'     => $item->getSnippet()->getTitle(),
            'thumbnail' => $item->getSnippet()->getThumbnails()->getHigh()->getUrl(),
            'viewers'   => $concurrentViewers,
        ];
    }

    /**
     * Modifier le titre / la description d'une vidéo existante (le fichier vidéo
     * lui-même ne peut pas être remplacé via l'API — seule la métadonnée est modifiable).
     */
    public function updateVideo(string $videoId, string $title, string $description = ''): void
    {
        $youtube = $this->authenticatedYoutube();
        $response = $youtube->videos->listVideos('snippet', ['id' => $videoId]);

        if (empty($response['items'])) {
            throw new Exception('Vidéo introuvable.');
        }

        $video = $response['items'][0];
        $snippet = $video->getSnippet();
        $snippet->setTitle($title);
        $snippet->setDescription($description);
        $video->setSnippet($snippet);

        $youtube->videos->update('snippet', $video);
    }

    /**
     * Supprimer une vidéo
     */
    public function delete(string $videoId): void
    {
        $this->authenticatedYoutube()->videos->delete($videoId);
    }

    /**
     * Convertit une durée ISO 8601 (ex: PT24M10S) en format lisible (ex: 24:10)
     */
    protected function formatDuration(string $iso8601): string
    {
        $interval = new \DateInterval($iso8601);
        $h = $interval->h + ($interval->days ?? 0) * 24;
        $m = $interval->i;
        $s = $interval->s;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }
}