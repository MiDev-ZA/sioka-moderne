<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Données de la vue d'ensemble, issues de l'API Google Analytics Data (GA4).
 *
 * Configuration attendue (config/services.php) :
 *   'google_analytics' => [
 *       'id'          => env('GOOGLE_ANALYTICS_ID'),            // G-XXXXXXX (déjà utilisé par le partial)
 *       'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),   // identifiant numérique de la propriété GA4
 *       'credentials' => env('GOOGLE_ANALYTICS_CREDENTIALS', storage_path('app/analytics/service-account.json')),
 *   ],
 */
class GoogleAnalyticsService
{
    private const BASE_Y = 170;   // ligne de base des barres (viewBox 700x210)
    private const BAR_MAX = 160;  // hauteur maximale d'une barre

    private const DAYS = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
    private const DAYS_SHORT = [1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 7 => 'Dim'];
    private const MONTHS = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    private const MONTHS_SHORT = [1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aoû', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];

    private ?BetaAnalyticsDataClient $client = null;

    /**
     * Tout ce dont la vue a besoin. `available` vaut false si GA n'est pas configuré ou injoignable.
     */
    public function dashboard(): array
    {
        if (! config('services.google_analytics.property_id')) {
            return ['available' => false, 'reason' => 'GA_PROPERTY_ID est vide ou absent du .env (puis php artisan config:clear).'];
        }
        if (! is_file((string) config('services.google_analytics.credentials'))) {
            return ['available' => false, 'reason' => 'Fichier JSON introuvable : '.config('services.google_analytics.credentials')];
        }

        try {
            $main = Cache::remember('ga:dashboard:main:v2', now()->addMinutes(15), fn () => $this->buildMain());
            $live = Cache::remember('ga:dashboard:live', now()->addMinute(), fn () => $this->buildLive());
        } catch (Throwable $e) {
            Log::warning('Google Analytics indisponible : '.$e->getMessage());

            return ['available' => false, 'reason' => get_class($e).' : '.$e->getMessage()];
        }

        $peak = max(1, $main['peak_daily_users']);
        $ratio = $live['count'] > 0 ? min(1, $live['count'] / max($live['count'], $peak)) : 0;
        $trend = $this->points($main['users_7'], 200, 44, 4);

        return [
            'available' => true,
            'live' => [
                'count' => $live['count'],
                'spark' => $live['spark'],
                'ring_offset' => round(402 * (1 - $ratio), 1),
                'trend_points' => $trend,
                'trend_area' => $trend ? 'M'.str_replace(' ', ' L', $trend).' L200,44 L0,44 Z' : '',
                'trend_label' => $main['cards']['users']['delta']['label'],
            ],
            'cards' => $main['cards'],
            'visits' => $main['visits'],
            'top' => $main['top'],
            'interactions' => $main['interactions'],
        ];
    }

    // ───────────────────────── Construction des données ─────────────────────────

    private function buildMain(): array
    {
        $today = CarbonImmutable::now()->startOfDay();

        // Totaux (visiteurs uniques non additionnables d'un jour à l'autre → une requête par période)
        $t7 = $this->totals($today->subDays(6), $today);
        $p7 = $this->totals($today->subDays(13), $today->subDays(7));
        $t30 = $this->totals($today->subDays(29), $today);
        $p30 = $this->totals($today->subDays(59), $today->subDays(30));
        $yStart = $today->startOfMonth()->subMonths(11);
        $tY = $this->totals($yStart, $today);
        $pY = $this->totals($yStart->subMonths(12), $yStart->subDay());

        // Série quotidienne sur 30 jours
        $daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $daily[$today->subDays($i)->format('Ymd')] = ['u' => 0, 'p' => 0, 'd' => 0];
        }
        foreach ($this->run(['date'], ['activeUsers', 'screenPageViews', 'averageSessionDuration'], $today->subDays(29), $today) as $r) {
            if (isset($daily[$r['d'][0]])) {
                $daily[$r['d'][0]] = ['u' => (int) $r['m'][0], 'p' => (int) $r['m'][1], 'd' => (float) $r['m'][2]];
            }
        }
        $last7 = array_slice($daily, -7, 7, true);
        $usersDaily7 = array_column($last7, 'u');

        // Barres par jour de semaine : 7 derniers jours / cumul des 30 derniers jours
        $byDay = [];
        foreach (self::DAYS as $iso => $_) {
            $byDay[$iso] = ['u7' => 0, 'p7' => 0, 'u30' => 0, 'p30' => 0];
        }
        for ($i = 0; $i < 30; $i++) {
            $date = $today->subDays($i);
            $row = $daily[$date->format('Ymd')];
            $iso = $date->dayOfWeekIso;
            $byDay[$iso]['u30'] += $row['u'];
            $byDay[$iso]['p30'] += $row['p'];
            if ($i < 7) {
                $byDay[$iso]['u7'] = $row['u'];
                $byDay[$iso]['p7'] = $row['p'];
            }
        }
        $max7 = max(1, ...array_merge(array_column($byDay, 'u7'), array_column($byDay, 'p7')));
        $max30 = max(1, ...array_merge(array_column($byDay, 'u30'), array_column($byDay, 'p30')));
        $days = [];
        foreach ($byDay as $iso => $v) {
            $days[] = $v + [
                'label' => self::DAYS[$iso],
                'short' => self::DAYS_SHORT[$iso],
                'y7u' => $this->y($v['u7'], $max7), 'h7u' => $this->h($v['u7'], $max7),
                'y7p' => $this->y($v['p7'], $max7), 'h7p' => $this->h($v['p7'], $max7),
                'y30u' => $this->y($v['u30'], $max30), 'h30u' => $this->h($v['u30'], $max30),
                'y30p' => $this->y($v['p30'], $max30), 'h30p' => $this->h($v['p30'], $max30),
            ];
        }

        // 12 derniers mois
        $monthly = [];
        for ($i = 0; $i < 12; $i++) {
            $monthly[$yStart->addMonths($i)->format('Ym')] = ['u' => 0, 'p' => 0];
        }
        foreach ($this->run(['yearMonth'], ['activeUsers', 'screenPageViews'], $yStart, $today) as $r) {
            if (isset($monthly[$r['d'][0]])) {
                $monthly[$r['d'][0]] = ['u' => (int) $r['m'][0], 'p' => (int) $r['m'][1]];
            }
        }
        $maxY = max(1, ...array_merge(array_column($monthly, 'u'), array_column($monthly, 'p')));
        $months = [];
        foreach ($monthly as $ym => $v) {
            $n = (int) substr($ym, 4, 2);
            $months[] = $v + [
                'label' => self::MONTHS[$n].' '.substr($ym, 0, 4),
                'short' => self::MONTHS_SHORT[$n],
                'yu' => $this->y($v['u'], $maxY), 'hu' => $this->h($v['u'], $maxY),
                'yp' => $this->y($v['p'], $maxY), 'hp' => $this->h($v['p'], $maxY),
            ];
        }

        // Top contenus de la semaine
        $top = collect($this->run(['pageTitle'], ['screenPageViews'], $today->subDays(6), $today, 'screenPageViews', 8))
            ->reject(fn ($r) => in_array($r['d'][0], ['', '(not set)'], true))
            ->take(3)
            ->map(fn ($r) => ['title' => $r['d'][0], 'views' => (int) $r['m'][0]])
            ->values()
            ->all();

        // Interactions : événements personnalisés envoyés par le site (voir partials/analytics.blade.php)
        $interactions = $this->interactions($today);

        return [
            'interactions' => $interactions,
            'peak_daily_users' => (int) max(array_column($daily, 'u')),
            'users_7' => $usersDaily7,
            'cards' => [
                'users' => [
                    'value' => number_format($t7['users'], 0, ',', ' '),
                    'delta' => $this->delta($t7['users'], $p7['users']),
                    'spark' => $this->points($usersDaily7, 100, 28),
                ],
                'views' => [
                    'value' => number_format($t7['views'], 0, ',', ' '),
                    'delta' => $this->delta($t7['views'], $p7['views']),
                    'spark' => $this->points(array_column($last7, 'p'), 100, 28),
                ],
                'duration' => [
                    'value' => $this->duration($t7['duration']),
                    'delta' => $this->delta($t7['duration'], $p7['duration']),
                    'spark' => $this->points(array_column($last7, 'd'), 100, 28),
                ],
            ],
            'visits' => [
                'totals' => ['7' => $t7['sessions'], '30' => $t30['sessions'], 'year' => $tY['sessions']],
                'deltas' => [
                    '7' => $this->delta($t7['sessions'], $p7['sessions'])['label'],
                    '30' => $this->delta($t30['sessions'], $p30['sessions'])['label'],
                    'year' => $this->delta($tY['sessions'], $pY['sessions'])['label'],
                ],
                'days' => $days,
                'months' => $months,
            ],
            'top' => $top,
        ];
    }

    /** Nombre d'événements personnalisés sur 7 jours, avec l'évolution face aux 7 jours précédents. */
    private function interactions(CarbonImmutable $today): array
    {
        $labels = [
            'radio_play' => 'Écoutes radio',
            'article_click' => 'Clics articles',
            'video_click' => 'Clics vidéos',
            'podcast_play' => 'Lectures podcasts',
        ];

        $count = function (CarbonImmutable $from, CarbonImmutable $to) use ($labels): array {
            $out = array_fill_keys(array_keys($labels), 0);
            foreach ($this->run(['eventName'], ['eventCount'], $from, $to, 'eventCount', 100) as $r) {
                if (array_key_exists($r['d'][0], $out)) {
                    $out[$r['d'][0]] = (int) $r['m'][0];
                }
            }

            return $out;
        };

        $cur = $count($today->subDays(6), $today);
        $prev = $count($today->subDays(13), $today->subDays(7));

        $result = [];
        foreach ($labels as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'value' => number_format($cur[$key], 0, ',', ' '),
                'raw' => $cur[$key],
                'delta' => $this->delta($cur[$key], $prev[$key]),
            ];
        }

        return $result;
    }

    private function buildLive(): array
    {
        $property = 'properties/'.config('services.google_analytics.property_id');
        $metric = new Metric(['name' => 'activeUsers']);

        $count = 0;
        $total = $this->client()->runRealtimeReport(
            (new RunRealtimeReportRequest())->setProperty($property)->setMetrics([$metric])
        );
        foreach ($total->getRows() as $row) {
            $count = (int) $row->getMetricValues()[0]->getValue();
        }

        // Visiteurs actifs minute par minute sur les 30 dernières minutes
        $series = array_fill(0, 30, 0);
        $byMinute = $this->client()->runRealtimeReport(
            (new RunRealtimeReportRequest())
                ->setProperty($property)
                ->setDimensions([new Dimension(['name' => 'minutesAgo'])])
                ->setMetrics([$metric])
        );
        foreach ($byMinute->getRows() as $row) {
            $ago = (int) $row->getDimensionValues()[0]->getValue();
            if ($ago >= 0 && $ago < 30) {
                $series[29 - $ago] = (int) $row->getMetricValues()[0]->getValue();
            }
        }

        return ['count' => $count, 'spark' => $this->points($series, 100, 28)];
    }

    // ───────────────────────────── Appels API ─────────────────────────────

    private function client(): BetaAnalyticsDataClient
    {
        return $this->client ??= new BetaAnalyticsDataClient([
            'credentials' => config('services.google_analytics.credentials'),
        ]);
    }

    /** @return array<int, array{d: string[], m: float[]}> */
    private function run(array $dimensions, array $metrics, CarbonImmutable $from, CarbonImmutable $to, ?string $orderByMetric = null, int $limit = 0): array
    {
        $request = (new RunReportRequest())
            ->setProperty('properties/'.config('services.google_analytics.property_id'))
            ->setDateRanges([new DateRange(['start_date' => $from->format('Y-m-d'), 'end_date' => $to->format('Y-m-d')])])
            ->setDimensions(array_map(fn ($n) => new Dimension(['name' => $n]), $dimensions))
            ->setMetrics(array_map(fn ($n) => new Metric(['name' => $n]), $metrics));

        if ($orderByMetric) {
            $request->setOrderBys([
                (new OrderBy())->setDesc(true)->setMetric((new MetricOrderBy())->setMetricName($orderByMetric)),
            ]);
        }
        if ($limit > 0) {
            $request->setLimit($limit);
        }

        $rows = [];
        foreach ($this->client()->runReport($request)->getRows() as $row) {
            $d = [];
            foreach ($row->getDimensionValues() as $v) {
                $d[] = $v->getValue();
            }
            $m = [];
            foreach ($row->getMetricValues() as $v) {
                $m[] = (float) $v->getValue();
            }
            $rows[] = ['d' => $d, 'm' => $m];
        }

        return $rows;
    }

    private function totals(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $m = $this->run([], ['activeUsers', 'screenPageViews', 'sessions', 'averageSessionDuration'], $from, $to)[0]['m'] ?? [0, 0, 0, 0];

        return ['users' => (int) $m[0], 'views' => (int) $m[1], 'sessions' => (int) $m[2], 'duration' => (float) $m[3]];
    }

    // ───────────────────────────── Utilitaires ─────────────────────────────

    private function delta(float $current, float $previous): array
    {
        if ($previous <= 0) {
            return ['label' => '—', 'up' => true];
        }
        $pct = ($current - $previous) / $previous * 100;

        return ['label' => ($pct >= 0 ? '▲ ' : '▼ ').number_format(abs($pct), 1, ',', ' ').'%', 'up' => $pct >= 0];
    }

    private function duration(float $seconds): string
    {
        $s = (int) round($seconds);

        return intdiv($s, 60).' min '.str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT).' s';
    }

    /** Points d'une polyline (viewBox $w x $h) à partir d'une série de valeurs. */
    private function points(array $values, int $w, int $h, int $pad = 3): string
    {
        $values = array_values($values);
        $n = count($values);
        if ($n < 2) {
            return '';
        }
        $min = min($values);
        $range = max(max($values) - $min, 1);
        $pts = [];
        foreach ($values as $i => $v) {
            $x = round($i * $w / ($n - 1), 1);
            $y = round($h - $pad - (($v - $min) / $range) * ($h - 2 * $pad), 1);
            $pts[] = $x.','.$y;
        }

        return implode(' ', $pts);
    }

    private function h(float $value, float $max): int
    {
        return $value > 0 ? max(2, (int) round($value / $max * self::BAR_MAX)) : 1;
    }

    private function y(float $value, float $max): int
    {
        return self::BASE_Y - $this->h($value, $max);
    }
}