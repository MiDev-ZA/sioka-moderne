<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\BatchRunReportsRequest;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Données d'audience GA4 pour la vue d'ensemble : pays, villes, régions, appareils,
 * sources de trafic, nouveaux vs récurrents, pages d'atterrissage, sources détaillées,
 * heures de pic et indicateurs d'engagement (7 jours).
 *
 * Trois appels API (2 batchs de rapports + 1 rapport comparatif),
 * mis en cache 10 minutes. En cas d'erreur, retourne [] : la vue masque alors
 * simplement la section.
 */
class GoogleAnalyticsAudience
{
    private const CACHE_KEY = 'ga.audience.7d.v2';
    private const TTL = 600; // secondes

    public function fetch(): array
    {
        if ($cached = Cache::get(self::CACHE_KEY)) {
            return $cached;
        }

        try {
            $data = $this->build();
            Cache::put(self::CACHE_KEY, $data, self::TTL);

            return $data;
        } catch (Throwable $e) {
            Log::warning('GA audience indisponible : ' . $e->getMessage());

            return [];
        }
    }

    private function build(): array
    {
        $client = $this->client();
        $property = 'properties/' . config('services.google_analytics.property_id');

        // 1) Cinq rapports en un seul appel (maximum autorisé par batchRunReports)
        $batch = $client->batchRunReports(new BatchRunReportsRequest([
            'property' => $property,
            'requests' => [
                $this->listRequest(['country', 'countryId']),
                $this->listRequest(['city']),
                $this->listRequest(['deviceCategory']),
                $this->listRequest(['sessionDefaultChannelGroup']),
                $this->listRequest(['hour']),
            ],
        ]));

        $reports = iterator_to_array($batch->getReports(), false);
        [$countries, $cities, $devices, $channels, $hours] = array_map(fn ($r) => $this->rows($r), $reports);

        // 1 bis) Quatre rapports supplémentaires (second batch)
        $batch2 = $client->batchRunReports(new BatchRunReportsRequest([
            'property' => $property,
            'requests' => [
                $this->listRequest(['region']),
                $this->listRequest(['newVsReturning']),
                $this->listRequest(['landingPage'], 100, 'sessions'),
                $this->listRequest(['sessionSource'], 100, 'sessions'),
            ],
        ]));

        $reports2 = iterator_to_array($batch2->getReports(), false);
        [$regions, $newReturning, $landings, $sources] = array_map(fn ($r) => $this->rows($r), $reports2);

        // 2) Indicateurs d'engagement, période courante vs précédente
        $kpiReport = $client->runReport(new RunReportRequest([
            'property' => $property,
            'date_ranges' => [
                new DateRange(['start_date' => '6daysAgo', 'end_date' => 'today', 'name' => 'current']),
                new DateRange(['start_date' => '13daysAgo', 'end_date' => '7daysAgo', 'name' => 'previous']),
            ],
            'metrics' => [
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'newUsers']),
                new Metric(['name' => 'engagementRate']),
                new Metric(['name' => 'bounceRate']),
            ],
        ]));

        return [
            'kpis' => $this->buildKpis($this->rows($kpiReport)),
            'countries' => $this->toList($countries, fn ($d) => $this->countryName($d[0], $d[1] ?? '')),
            'cities' => $this->toList($cities, fn ($d) => $d[0] === '(not set)' ? 'Non déterminée' : $d[0]),
            'devices' => $this->toList($devices, fn ($d) => $this->deviceLabel($d[0]), 4),
            'channels' => $this->toList($channels, fn ($d) => $this->channelLabel($d[0])),
            'regions' => $this->toList($regions, fn ($d) => $d[0] === '(not set)' ? 'Non déterminée' : $d[0]),
            'new_returning' => $this->toList($newReturning, fn ($d) => $this->newReturningLabel($d[0]), 3),
            'landing_pages' => $this->toList($landings, fn ($d) => $this->landingLabel($d[0])),
            'sources' => $this->toList($sources, fn ($d) => $this->sourceLabel($d[0])),
            'hours' => $this->buildHours($hours),
            'peak_hour' => $this->peakHour($hours),
        ];
    }

    private function client(): BetaAnalyticsDataClient
    {
        // Reprenez ici les mêmes identifiants que votre service GA existant.
        return new BetaAnalyticsDataClient([
            'credentials' => config('services.google_analytics.credentials'),
        ]);
    }

    private function listRequest(array $dimensions, int $limit = 100, string $metric = 'activeUsers'): RunReportRequest
    {
        return new RunReportRequest([
            'date_ranges' => [new DateRange(['start_date' => '6daysAgo', 'end_date' => 'today'])],
            'dimensions' => array_map(fn ($name) => new Dimension(['name' => $name]), $dimensions),
            'metrics' => [new Metric(['name' => $metric])],
            'order_bys' => [new OrderBy([
                'metric' => new MetricOrderBy(['metric_name' => $metric]),
                'desc' => true,
            ])],
            'limit' => $limit,
        ]);
    }

    /** @return array<int, array{0: string[], 1: float[]}> */
    private function rows($report): array
    {
        $out = [];
        foreach ($report->getRows() as $row) {
            $dims = [];
            foreach ($row->getDimensionValues() as $d) {
                $dims[] = $d->getValue();
            }
            $mets = [];
            foreach ($row->getMetricValues() as $m) {
                $mets[] = (float) $m->getValue();
            }
            $out[] = [$dims, $mets];
        }

        return $out;
    }

    private function toList(array $rows, callable $labelFn, int $top = 6): array
    {
        $total = array_sum(array_map(fn ($r) => $r[1][0], $rows));
        if ($total <= 0) {
            return [];
        }

        return collect($rows)->take($top)->map(fn ($r) => [
            'label' => $labelFn($r[0]),
            'value' => (int) $r[1][0],
            'pct' => round($r[1][0] / $total * 100, 1),
        ])->all();
    }

    private function buildHours(array $rows): array
    {
        $hours = array_fill(0, 24, 0);
        foreach ($rows as [$dims, $mets]) {
            $h = (int) $dims[0];
            if ($h >= 0 && $h <= 23) {
                $hours[$h] = (int) $mets[0];
            }
        }

        return $hours;
    }

    private function peakHour(array $rows): ?int
    {
        $hours = $this->buildHours($rows);

        return max($hours) > 0 ? array_search(max($hours), $hours, true) : null;
    }

    private function buildKpis(array $rows): array
    {
        $cur = array_fill(0, 4, 0.0);
        $prev = array_fill(0, 4, 0.0);
        foreach ($rows as [$dims, $mets]) {
            if ($dims[0] === 'current') {
                $cur = $mets;
            } elseif ($dims[0] === 'previous') {
                $prev = $mets;
            }
        }

        $pct = fn (float $v) => number_format($v * 100, 1, ',', ' ') . ' %';
        $int = fn (float $v) => number_format($v, 0, ',', ' ');

        return [
            'sessions' => [
                'label' => 'Sessions',
                'value' => $int($cur[0]),
                'delta' => $this->delta($cur[0], $prev[0]),
            ],
            'new_users' => [
                'label' => 'Nouveaux visiteurs',
                'value' => $int($cur[1]),
                'delta' => $this->delta($cur[1], $prev[1]),
            ],
            'engagement' => [
                'label' => "Taux d'engagement",
                'value' => $pct($cur[2]),
                'delta' => $this->delta($cur[2], $prev[2], points: true),
            ],
            'bounce' => [
                'label' => 'Taux de rebond',
                'value' => $pct($cur[3]),
                'delta' => $this->delta($cur[3], $prev[3], points: true, lowerIsBetter: true),
            ],
        ];
    }

    private function delta(float $cur, float $prev, bool $points = false, bool $lowerIsBetter = false): array
    {
        if (! $points && $prev <= 0) {
            return ['up' => true, 'label' => $cur > 0 ? 'Nouveau' : '—'];
        }

        $diff = $points ? ($cur - $prev) * 100 : (($cur - $prev) / $prev) * 100;
        $improved = $lowerIsBetter ? $diff <= 0 : $diff >= 0;

        return [
            'up' => $improved,
            'label' => ($diff >= 0 ? '▲ ' : '▼ ') . number_format(abs($diff), 1, ',', ' ') . ($points ? ' pt' : ' %'),
        ];
    }

    private function countryName(string $name, string $code): string
    {
        if (strlen($code) === 2 && class_exists(\Locale::class)) {
            $fr = \Locale::getDisplayRegion('-' . $code, 'fr');
            if ($fr && $fr !== $code) {
                return $fr;
            }
        }

        return $name === '(not set)' ? 'Non déterminé' : $name;
    }

    private function deviceLabel(string $v): string
    {
        return match (strtolower($v)) {
            'desktop' => 'Ordinateur',
            'mobile' => 'Mobile',
            'tablet' => 'Tablette',
            'smart tv' => 'Télévision',
            default => $v === '(not set)' ? 'Non déterminé' : ucfirst($v),
        };
    }

    private function newReturningLabel(string $v): string
    {
        return match ($v) {
            'new' => 'Nouveaux visiteurs',
            'returning' => 'Visiteurs récurrents',
            default => 'Non déterminé',
        };
    }

    private function landingLabel(string $v): string
    {
        if ($v === '/') {
            return 'Accueil';
        }

        return $v === '(not set)' ? 'Non déterminée' : mb_strimwidth($v, 0, 60, '…');
    }

    private function sourceLabel(string $v): string
    {
        return match ($v) {
            '(direct)' => 'Accès direct',
            '(not set)', '(none)' => 'Non déterminée',
            default => $v,
        };
    }

    private function channelLabel(string $v): string
    {
        return match ($v) {
            'Direct' => 'Accès direct',
            'Organic Search' => 'Moteurs de recherche',
            'Organic Social' => 'Réseaux sociaux',
            'Paid Social' => 'Réseaux sociaux (payant)',
            'Paid Search' => 'Recherche payante',
            'Referral' => 'Sites référents',
            'Email' => 'E-mail',
            'Organic Video' => 'Vidéo',
            'Display' => 'Affichage',
            'Unassigned' => 'Non attribué',
            default => $v,
        };
    }
}