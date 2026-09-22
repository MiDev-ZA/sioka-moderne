<?php

use App\Services\YoutubeService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Vérifie toutes les 3 minutes si la chaîne SIOKA est en direct sur YouTube,
// et met le résultat en cache. La page publique lit ce cache — jamais
// d'appel direct à l'API à chaque visite (coût de 100 unités de quota).
Schedule::call(function () {
    try {
        $live = app(YoutubeService::class)->getLiveBroadcast();
        Cache::put('youtube_live_status', $live, now()->addMinutes(5));
    } catch (\Exception $e) {
        // Token YouTube absent/expiré ou API indisponible : on ignore,
        // la page publique retombera automatiquement sur la dernière vidéo.
    }
})->everyThreeMinutes();