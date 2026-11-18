@extends('layouts.dashboard-layout')

@section('title', "Tableau de bord - SIOKA")

@section('content')
<div id="export-zone">
    <div class="welcome">
        <div>
            <h1>Bonjour, <em>{{ auth()->user()->name }}</em> <span>👋</span></h1>
            <p>Voici un aperçu de l'activité SIOKA aujourd'hui.</p>
        </div>
        <div style="display:flex; gap:.6rem; align-items:center; flex-wrap:wrap;">
            <div class="date-pill">
                {{ ucfirst(\Carbon\Carbon::now()->locale('fr')->translatedFormat('l j F Y')) }}
            </div>
            {{-- Bouton exclu du PDF grâce à data-html2canvas-ignore --}}
            <button type="button" id="btnPdf" class="date-pill" data-html2canvas-ignore="true"
                title="Télécharger les statistiques au format PDF"
                aria-label="Exporter les statistiques en PDF"
                style="cursor:pointer; border:0; display:inline-flex; align-items:center; justify-content:center; gap:.55rem; padding:.7rem 1.15rem; border-radius:12px; background:linear-gradient(135deg, #b31983 0%, #0082ca 100%); color:#fff; font-size:.88rem; font-weight:700; letter-spacing:.01em; box-shadow:0 5px 16px rgba(179,25,131,.25); transition:transform .2s ease, box-shadow .2s ease, opacity .2s ease;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <path d="M14 2v6h6" />
                    <path d="M8 13h2a1.5 1.5 0 0 1 0 3H8v-3Z" />
                    <path d="M14 16v-3h1a1.5 1.5 0 0 1 0 3h-1Z" />
                    <path d="M20 13h-2v3" />
                    <path d="M18 14.5h1.5" />
                </svg>
                <span>Exporter en PDF</span>
            </button>
        </div>
    </div>

    @if ($ga['available'])
    <div class="stats">
        <div class="stat" style="--stat-glow:var(--magenta-glow); --stat-color:var(--magenta);">
            <div class="stat-top">
                <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5 6 9H2v6h4l5 4V5Z" /><path d="M15.5 8.5a5 5 0 0 1 0 7" /></svg></div>
                <span class="stat-delta">30 min</span>
            </div>
            <div class="stat-value">{{ number_format($ga['live']['count'], 0, ',', ' ') }}</div>
            <div class="stat-label">Visiteurs en direct</div>
            <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
                <polyline points="{{ $ga['live']['spark'] }}" fill="none" stroke="var(--magenta)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <div class="stat" style="--stat-glow:var(--bleu-glow); --stat-color:var(--bleu);">
            <div class="stat-top">
                <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" /><path d="M20 21a8 8 0 0 0-16 0" /></svg></div>
                <span class="stat-delta {{ $ga['cards']['users']['delta']['up'] ? 'up' : 'down' }}">{{ $ga['cards']['users']['delta']['label'] }}</span>
            </div>
            <div class="stat-value">{{ $ga['cards']['users']['value'] }}</div>
            <div class="stat-label">Visiteurs · 7 jours</div>
            <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
                <polyline points="{{ $ga['cards']['users']['spark'] }}" fill="none" stroke="var(--bleu)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <div class="stat" style="--stat-glow:rgba(201,162,39,.18); --stat-color:var(--or);">
            <div class="stat-top">
                <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" /><path d="M14 4v5h5" /></svg></div>
                <span class="stat-delta {{ $ga['cards']['views']['delta']['up'] ? 'up' : 'down' }}">{{ $ga['cards']['views']['delta']['label'] }}</span>
            </div>
            <div class="stat-value">{{ $ga['cards']['views']['value'] }}</div>
            <div class="stat-label">Pages vues · 7 jours</div>
            <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
                <polyline points="{{ $ga['cards']['views']['spark'] }}" fill="none" stroke="var(--or)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <div class="stat" style="--stat-glow:var(--vert-glow); --stat-color:var(--vert);">
            <div class="stat-top">
                <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 8v4l3 2" /></svg></div>
                <span class="stat-delta {{ $ga['cards']['duration']['delta']['up'] ? 'up' : 'down' }}">{{ $ga['cards']['duration']['delta']['label'] }}</span>
            </div>
            <div class="stat-value">{{ $ga['cards']['duration']['value'] }}</div>
            <div class="stat-label">Durée moy. de session</div>
            <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
                <polyline points="{{ $ga['cards']['duration']['spark'] }}" fill="none" stroke="var(--vert)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>
        <section class="panel live-panel">
            <div class="live-top">
                <span class="live-title">SIOKA en direct</span>
            </div>

            <div class="radial">
                <svg viewBox="0 0 150 150">
                    <circle cx="75" cy="75" r="64" fill="none" stroke="var(--glass-bord)"
                        stroke-width="10" />
                    <circle cx="75" cy="75" r="64" fill="none" stroke="url(#g1)"
                        stroke-width="10" stroke-linecap="round" stroke-dasharray="402"
                        stroke-dashoffset="{{ $ga['live']['ring_offset'] }}" />
                    <defs>
                        <linearGradient id="g1" x1="0" y1="0" x2="1"
                            y2="1">
                            <stop offset="0%" stop-color="var(--magenta)" />
                            <stop offset="100%" stop-color="var(--bleu)" />
                        </linearGradient>
                    </defs>
                </svg>
                <div class="radial-center">
                    <strong>{{ number_format($ga['live']['count'], 0, ',', ' ') }}</strong>
                    <span>Visiteurs</span>
                </div>
            </div>

            <div class="trend-label"><span>Tendance 7 jours</span><span>{{ $ga['live']['trend_label'] }}</span></div>
            <svg class="trend" viewBox="0 0 200 44" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="g2" x1="0" y1="0" x2="0"
                        y2="1">
                        <stop offset="0%" stop-color="var(--bleu)" stop-opacity="0.35" />
                        <stop offset="100%" stop-color="var(--bleu)" stop-opacity="0" />
                    </linearGradient>
                </defs>
                <path d="{{ $ga['live']['trend_area'] }}"
                    fill="url(#g2)" />
                <polyline points="{{ $ga['live']['trend_points'] }}" fill="none"
                    stroke="var(--bleu)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            {{-- Lecteur radio : exclu du PDF --}}
            <div class="mini-player" data-html2canvas-ignore="true">
                        <polygon points="6 4 20 12 6 20 6 4" />
                    </svg></button>
                <div class="mini-text">
                    <p>SIOKA Webradio</p>
                    <span>FM 106 Mhz · En direct</span>
                </div>
            </div>
        </section>

    <section>
        <div class="dash-sec-head">
            <div class="dash-sec-title">Interactions · 7 jours</div>
        </div>
        <div class="stats">
            @php
                $icons = [
                    'radio_play' => '<path d="M11 5 6 9H2v6h4l5 4V5Z" /><path d="M15.5 8.5a5 5 0 0 1 0 7" />',
                    'article_click' => '<path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" /><path d="M14 4v5h5" />',
                    'video_click' => '<rect x="3" y="5" width="18" height="14" rx="2" /><path d="m10 9 5 3-5 3V9Z" />',
                    'podcast_play' => '<circle cx="12" cy="12" r="10" /><path d="m10 8 6 4-6 4V8Z" />',
                ];
                $colors = [
                    'radio_play' => ['var(--magenta-glow)', 'var(--magenta)'],
                    'article_click' => ['var(--bleu-glow)', 'var(--bleu)'],
                    'video_click' => ['rgba(201,162,39,.18)', 'var(--or)'],
                    'podcast_play' => ['var(--vert-glow)', 'var(--vert)'],
                ];
            @endphp
            @foreach ($ga['interactions'] as $key => $item)
                <div class="stat" style="--stat-glow:{{ $colors[$key][0] }}; --stat-color:{{ $colors[$key][1] }};">
                    <div class="stat-top">
                        <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $icons[$key] !!}</svg></div>
                        <span class="stat-delta {{ $item['delta']['up'] ? 'up' : 'down' }}">{{ $item['delta']['label'] }}</span>
                    </div>
                    <div class="stat-value">{{ $item['value'] }}</div>
                    <div class="stat-label">{{ $item['label'] }}</div>
                </div>
            @endforeach
        </div>
        @if (collect($ga['interactions'])->sum('raw') === 0)
            <p style="color:var(--plomb); font-size:.85rem; margin-top:.75rem;">Aucun clic enregistré pour le moment. Les compteurs apparaissent quelques heures après les premiers événements envoyés par le site.</p>
        @endif
    </section>

    <section>
        <div class="dash-sec-head">
            <div class="dash-sec-title">Statistiques de visite</div>
        </div>
        <div class="panel visits-panel" id="visitsPanel">
            <div class="visits-head">
                <div class="visits-total">
                    <strong id="visitsTotal" data-t7="{{ $ga['visits']['totals']['7'] }}" data-t30="{{ $ga['visits']['totals']['30'] }}" data-tyear="{{ $ga['visits']['totals']['year'] }}">0</strong>
                    <span>Visites <span id="visitsPeriodLabel">cette semaine</span> · <span id="visitsDelta" style="color:{{ str_starts_with($ga['visits']['deltas']['7'], '▼') ? 'var(--magenta)' : 'var(--vert)' }}; font-weight:700;" data-d7="{{ $ga['visits']['deltas']['7'] }}" data-d30="{{ $ga['visits']['deltas']['30'] }}" data-dyear="{{ $ga['visits']['deltas']['year'] }}">{{ $ga['visits']['deltas']['7'] }}</span> vs période précédente</span>
                </div>
                <div class="visits-period">
                    <button class="on" data-period="7">7 jours</button>
                    <button data-period="30">30 jours</button>
                    <button data-period="year">Année dernière</button>
                </div>
            </div>

            <div class="visits-chart-wrap">
                <svg class="visits-chart" viewBox="0 0 700 210" preserveAspectRatio="xMidYMid meet"
                    role="img" aria-label="Visiteurs uniques et pages vues">
                    <defs>
                        <linearGradient id="gradBleu" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="var(--bleu)" stop-opacity="1" />
                            <stop offset="100%" stop-color="var(--bleu)" stop-opacity=".25" />
                        </linearGradient>
                        <linearGradient id="gradMagenta" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="var(--magenta)" stop-opacity="1" />
                            <stop offset="100%" stop-color="var(--magenta)" stop-opacity=".25" />
                        </linearGradient>
                        <linearGradient id="gradTrend" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="var(--magenta)" />
                            <stop offset="100%" stop-color="var(--bleu)" />
                        </linearGradient>
                        <filter id="barGlow" x="-60%" y="-60%" width="220%" height="220%">
                            <feGaussianBlur stdDeviation="3.5" result="blur" />
                            <feMerge>
                                <feMergeNode in="blur" />
                                <feMergeNode in="SourceGraphic" />
                            </feMerge>
                        </filter>
                    </defs>

                    <line class="grid-line" x1="0" y1="170" x2="700" y2="170" stroke="var(--glass-bord)" stroke-width="1" />
                    <line class="grid-line grid-line-dash" x1="0" y1="113" x2="700" y2="113" stroke="var(--glass-bord)" stroke-width="1" stroke-dasharray="3 6" />
                    <line class="grid-line grid-line-dash" x1="0" y1="56" x2="700" y2="56" stroke="var(--glass-bord)" stroke-width="1" stroke-dasharray="3 6" />

                    <g class="bar-set" id="daysSet">
                        @foreach ($ga['visits']['days'] as $i => $d)
                            <g class="bar-group" style="--i:{{ $i }}" data-day="{{ $d['label'] }}" data-u7="{{ $d['u7'] }}" data-p7="{{ $d['p7'] }}" data-u30="{{ $d['u30'] }}" data-p30="{{ $d['p30'] }}">
                                <rect class="bar bar-u" filter="url(#barGlow)" x="{{ 25 + 100 * $i }}" y="{{ $d['y7u'] }}" width="22" height="{{ $d['h7u'] }}" rx="4" fill="url(#gradBleu)" data-y7="{{ $d['y7u'] }}" data-h7="{{ $d['h7u'] }}" data-y30="{{ $d['y30u'] }}" data-h30="{{ $d['h30u'] }}" />
                                <rect class="bar bar-p" filter="url(#barGlow)" x="{{ 53 + 100 * $i }}" y="{{ $d['y7p'] }}" width="22" height="{{ $d['h7p'] }}" rx="4" fill="url(#gradMagenta)" data-y7="{{ $d['y7p'] }}" data-h7="{{ $d['h7p'] }}" data-y30="{{ $d['y30p'] }}" data-h30="{{ $d['h30p'] }}" />
                                <text x="{{ 50 + 100 * $i }}" y="190" text-anchor="middle" font-size="11" fill="var(--plomb)">{{ $d['short'] }}</text>
                            </g>
                        @endforeach
                    </g>

                    <g class="bar-set" id="monthsSet" style="display:none">
                        @foreach ($ga['visits']['months'] as $i => $m)
                            <g class="bar-group" style="--i:{{ $i }}" data-day="{{ $m['label'] }}" data-uyear="{{ $m['u'] }}" data-pyear="{{ $m['p'] }}">
                                <rect class="bar bar-u" filter="url(#barGlow)" x="{{ round(17 + 58.33 * $i, 1) }}" y="{{ $m['yu'] }}" width="10" height="{{ $m['hu'] }}" rx="3" fill="url(#gradBleu)" data-yyear="{{ $m['yu'] }}" data-hyear="{{ $m['hu'] }}" />
                                <rect class="bar bar-p" filter="url(#barGlow)" x="{{ round(31 + 58.33 * $i, 1) }}" y="{{ $m['yp'] }}" width="10" height="{{ $m['hp'] }}" rx="3" fill="url(#gradMagenta)" data-yyear="{{ $m['yp'] }}" data-hyear="{{ $m['hp'] }}" />
                                <text x="{{ round(29 + 58.33 * $i, 1) }}" y="190" text-anchor="middle" font-size="10.5" fill="var(--plomb)">{{ $m['short'] }}</text>
                            </g>
                        @endforeach
                    </g>

                    <polyline id="visitsTrend" class="trend-overlay" fill="none" stroke="url(#gradTrend)"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                        points="50,80 150,65 250,72 350,50 450,35 550,10 650,26" />
                </svg>
                <div class="visits-tooltip" id="visitsTooltip"></div>
            </div>

            <div class="visits-legend">
                <div class="visits-legend-item"><span class="visits-legend-dot" style="background:var(--bleu);"></span>Visiteurs uniques</div>
                <div class="visits-legend-item"><span class="visits-legend-dot" style="background:var(--magenta);"></span>Pages vues</div>
                <div class="visits-legend-item"><span class="visits-legend-dot" style="background:linear-gradient(90deg,var(--magenta),var(--bleu));"></span>Tendance</div>
            </div>
        </div>
    </section>

    <section>
        <div class="dash-sec-head">
            <div class="dash-sec-title">Top contenus de la semaine</div><a href="#"
                class="dash-sec-link" data-html2canvas-ignore="true">Statistiques</a>
        </div>
        <div class="rank-grid">
            @forelse ($ga['top'] as $i => $page)
                <div class="rank-card">
                    <span class="rank-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="rank-info">
                        <p>{{ $page['title'] }}</p><span>{{ number_format($page['views'], 0, ',', ' ') }} vues</span>
                    </div>
                </div>
            @empty
                <p style="color:var(--plomb);">Aucune donnée pour le moment.</p>
            @endforelse
        </div>
    </section>

    {{-- ── Audience GA4 : engagement, géographie, appareils, sources, heures de pic ── --}}
    @if (! empty($ga['audience']))
        @php
            $aud = $ga['audience'];
            $audIcons = [
                'sessions'   => '<path d="M22 12h-4l-3 9L9 3l-3 9H2" />',
                'new_users'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M19 8v6M22 11h-6" />',
                'engagement' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z" />',
                'bounce'     => '<path d="M9 14 4 9l5-5" /><path d="M4 9h10a6 6 0 0 1 6 6v5" />',
            ];
            $audColors = [
                'sessions'   => ['var(--bleu-glow)', 'var(--bleu)'],
                'new_users'  => ['var(--magenta-glow)', 'var(--magenta)'],
                'engagement' => ['var(--vert-glow)', 'var(--vert)'],
                'bounce'     => ['rgba(201,162,39,.18)', 'var(--or)'],
            ];
            $hours    = $aud['hours'] ?? [];
            $maxHour  = max(1, ...(count($hours) ? $hours : [1]));
            $peakHour = $aud['peak_hour'] ?? null;
        @endphp

        <section>
            <div class="dash-sec-head">
                <div class="dash-sec-title">Audience · 7 jours</div>
            </div>
            <div class="stats">
                @foreach ($aud['kpis'] as $key => $kpi)
                    <div class="stat" style="--stat-glow:{{ $audColors[$key][0] }}; --stat-color:{{ $audColors[$key][1] }};">
                        <div class="stat-top">
                            <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $audIcons[$key] !!}</svg></div>
                            <span class="stat-delta {{ $kpi['delta']['up'] ? 'up' : 'down' }}">{{ $kpi['delta']['label'] }}</span>
                        </div>
                        <div class="stat-value">{{ $kpi['value'] }}</div>
                        <div class="stat-label">{{ $kpi['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <div class="dash-sec-head">
                <div class="dash-sec-title">Provenance géographique · 7 jours</div>
            </div>
            <div class="grid2">
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Pays</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['countries'], 'color' => 'var(--bleu)'])
                </div>
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Villes</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['cities'], 'color' => 'var(--magenta)'])
                </div>
            </div>
        </section>

        <section>
            <div class="dash-sec-head">
                <div class="dash-sec-title">Appareils et sources · 7 jours</div>
            </div>
            <div class="grid2">
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Appareils</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['devices'], 'color' => 'var(--vert)'])
                </div>
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Sources de trafic</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['channels'], 'color' => 'var(--or)'])
                </div>
            </div>
        </section>

        <section>
            <div class="dash-sec-head">
                <div class="dash-sec-title">Régions et fidélité · 7 jours</div>
            </div>
            <div class="grid2">
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Régions</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['regions'] ?? [], 'color' => 'var(--bleu)'])
                </div>
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Nouveaux vs récurrents</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['new_returning'] ?? [], 'color' => 'var(--vert)'])
                </div>
            </div>
        </section>

        <section>
            <div class="dash-sec-head">
                <div class="dash-sec-title">Entrées et sources détaillées · 7 jours</div>
            </div>
            <div class="grid2">
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Pages d'atterrissage (sessions)</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['landing_pages'] ?? [], 'color' => 'var(--magenta)'])
                </div>
                <div class="panel panel-pad">
                    <p style="font-weight:700; margin-bottom:1rem;">Sources détaillées (sessions)</p>
                    @include('dashboard.partials.ga-bar-list', ['items' => $aud['sources'] ?? [], 'color' => 'var(--or)'])
                </div>
            </div>
        </section>

        <section>
            <div class="dash-sec-head">
                <div class="dash-sec-title">Heures d'audience · 7 jours</div>
            </div>
            <div class="panel panel-pad">
                @if (array_sum($hours) > 0)
                    <p style="color:var(--plomb); font-size:.88rem; margin-bottom:1rem;">
                        Pic d'audience à {{ str_pad($peakHour, 2, '0', STR_PAD_LEFT) }} h (heure du fuseau de la propriété GA4)
                    </p>
                    <div style="display:flex; align-items:flex-end; gap:3px; height:120px;">
                        @foreach ($hours as $h => $v)
                            <div title="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }} h · {{ number_format($v, 0, ',', ' ') }} visiteurs"
                                 style="flex:1; height:{{ max(3, round($v / $maxHour * 100)) }}%; border-radius:3px 3px 0 0; background:{{ $h === $peakHour ? 'var(--magenta)' : 'var(--bleu)' }}; opacity:{{ $h === $peakHour ? 1 : .55 }};"></div>
                        @endforeach
                    </div>
                    <div style="display:flex; justify-content:space-between; color:var(--plomb); font-size:.75rem; margin-top:.4rem;">
                        <span>0 h</span><span>6 h</span><span>12 h</span><span>18 h</span><span>23 h</span>
                    </div>
                @else
                    <p style="color:var(--plomb);">Aucune donnée pour le moment.</p>
                @endif
            </div>
        </section>
    @endif
    @else
        <p style="color:var(--plomb);">Les données Google Analytics sont momentanément indisponibles.</p>
        @if (config('app.debug') && ! empty($ga['reason']))
            <p style="color:var(--magenta); font-size:.85rem;">Cause (visible uniquement en mode debug) : {{ $ga['reason'] }}</p>
        @endif
    @endif
</div>{{-- /#export-zone --}}

{{-- Contraste renforcé pour le thème sombre : styles limités au tableau de bord --}}
<style>
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone {
        --plomb: #cbd5e1;
        --glass-bord: rgba(203, 213, 225, .20);
        color: #e2e8f0;
    }

    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .stat-value,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .radial-center strong,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .visits-total strong {
        color: #f8fafc;
    }

    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .stat-label,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .radial-center span,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .trend-label,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .visits-total > span,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .visits-legend,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .rank-info p,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .rank-info span,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .panel-pad p,
    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .dash-sec-title {
        color: #cbd5e1;
    }

    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .visits-tooltip {
        color: #f8fafc;
        background: #172033;
        border: 1px solid rgba(203, 213, 225, .22);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .35);
    }

    :where(
        html.dark,
        body.dark,
        .dark,
        html.dark-mode,
        body.dark-mode,
        .dark-mode,
        [data-theme="dark"],
        [data-bs-theme="dark"],
        [data-color-mode="dark"]
    ) #export-zone .date-pill:not(#btnPdf) {
        color: #e2e8f0;
    }
</style>
@endsection

@push('scripts')

<script>
    // ── Carte "Statistiques de visite" : version avancée ──────────
    (function () {
        const panel = document.getElementById('visitsPanel');
        if (!panel) return;

        const fmt = new Intl.NumberFormat('fr-FR');
        const totalEl = document.getElementById('visitsTotal');
        const deltaEl = document.getElementById('visitsDelta');
        const periodLabelEl = document.getElementById('visitsPeriodLabel');
        const trend = document.getElementById('visitsTrend');
        const tooltip = document.getElementById('visitsTooltip');
        const wrap = panel.querySelector('.visits-chart-wrap');
        const daysSet = document.getElementById('daysSet');
        const monthsSet = document.getElementById('monthsSet');
        const allGroups = panel.querySelectorAll('.bar-group');
        const periodButtons = panel.querySelectorAll('.visits-period button');

        function activeGroups(period) {
            return period === 'year' ? monthsSet.querySelectorAll('.bar-group') : daysSet.querySelectorAll('.bar-group');
        }

        // Compteur animé (ease-out)
        function animateNumber(el, from, to, duration) {
            const start = performance.now();
            function tick(now) {
                const p = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = fmt.format(Math.round(from + (to - from) * eased));
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }

        // Recalcule la courbe de tendance à partir du sommet des barres "pages vues" du jeu actif
        function refreshTrend(period) {
            const pts = Array.from(activeGroups(period)).map(g => {
                const bp = g.querySelector('.bar-p');
                const x = parseFloat(bp.getAttribute('x')) + parseFloat(bp.getAttribute('width')) / 2;
                const y = parseFloat(bp.getAttribute('y'));
                return x + ',' + y;
            });
            trend.setAttribute('points', pts.join(' '));
        }

        function setPeriod(period, animateCount) {
            const isYear = period === 'year';
            daysSet.style.display = isYear ? 'none' : '';
            monthsSet.style.display = isYear ? '' : 'none';

            activeGroups(period).forEach(g => {
                ['u', 'p'].forEach(k => {
                    const rect = g.querySelector('.bar-' + k);
                    rect.setAttribute('y', rect.getAttribute('data-y' + period));
                    rect.setAttribute('height', rect.getAttribute('data-h' + period));
                });
            });

            const targetTotal = parseInt(totalEl.getAttribute('data-t' + period), 10);
            const currentTotal = parseInt((totalEl.textContent || '0').replace(/\s/g, ''), 10) || 0;
            if (animateCount) animateNumber(totalEl, currentTotal, targetTotal, 700);
            else totalEl.textContent = fmt.format(targetTotal);

            deltaEl.textContent = deltaEl.getAttribute('data-d' + period);
            deltaEl.style.color = deltaEl.textContent.indexOf('▼') === 0 ? 'var(--magenta)' : 'var(--vert)';
            periodLabelEl.textContent = period === '7' ? 'cette semaine'
                : period === '30' ? 'ces 30 derniers jours'
                : "sur l'année dernière";

            // La courbe se redessine en douceur après la transition des barres
            setTimeout(() => refreshTrend(period), isYear ? 0 : 350);
            // Ré-anime l'entrée des barres du jeu qui vient d'apparaître
            if (isYear) {
                panel.classList.remove('is-ready');
                requestAnimationFrame(() => requestAnimationFrame(() => panel.classList.add('is-ready')));
            }
        }

        periodButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('on')) return;
                periodButtons.forEach(b => b.classList.remove('on'));
                btn.classList.add('on');
                setPeriod(btn.getAttribute('data-period'), true);
            });
        });

        // Tooltip interactif au survol des groupes de barres (jours ou mois)
        allGroups.forEach(g => {
            g.addEventListener('mouseenter', () => {
                const currentPeriod = panel.querySelector('.visits-period button.on').getAttribute('data-period');
                activeGroups(currentPeriod).forEach(o => o.classList.toggle('dim', o !== g));
                const u = fmt.format(parseInt(g.getAttribute('data-u' + currentPeriod), 10));
                const p = fmt.format(parseInt(g.getAttribute('data-p' + currentPeriod), 10));
                tooltip.innerHTML = '<strong>' + g.getAttribute('data-day') + '</strong>' +
                    '<span><i style="background:var(--bleu)"></i>Uniques : ' + u + '</span>' +
                    '<span><i style="background:var(--magenta)"></i>Pages vues : ' + p + '</span>';
                tooltip.classList.add('show');
            });
            g.addEventListener('mousemove', (e) => {
                const box = wrap.getBoundingClientRect();
                let left = e.clientX - box.left + 14;
                let top = e.clientY - box.top - 10;
                const maxLeft = box.width - 170;
                if (left > maxLeft) left = e.clientX - box.left - 170 - 14;
                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
            });
            g.addEventListener('mouseleave', () => {
                const currentPeriod = panel.querySelector('.visits-period button.on').getAttribute('data-period');
                activeGroups(currentPeriod).forEach(o => o.classList.remove('dim'));
                tooltip.classList.remove('show');
            });
        });

        // Animation d'entrée + premier affichage
        requestAnimationFrame(() => panel.classList.add('is-ready'));
        refreshTrend('7');
        animateNumber(totalEl, 0, parseInt(totalEl.getAttribute('data-t7'), 10), 1100);
    })();
</script>

{{-- ── Export PDF (html2pdf.js, 100 % côté navigateur) ─────────── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    (function () {
        const btn = document.getElementById('btnPdf');
        const zone = document.getElementById('export-zone');
        if (!btn || !zone) return;

        // Les attributs SVG (stroke, fill, stop-color) avec var(--x) ne sont pas lus par html2canvas :
        // on les remplace par les vraies couleurs, uniquement dans la copie utilisée pour la capture.
        const rootStyle = getComputedStyle(document.documentElement);
        const resolveVars = (v) => v.replace(/var\((--[\w-]+)\)/g, (_, name) => rootStyle.getPropertyValue(name).trim() || '#888');

        btn.addEventListener('click', async () => {
            if (typeof html2pdf === 'undefined') {
                alert("La bibliothèque d'export PDF n'a pas pu être chargée. Vérifiez votre connexion et réessayez.");
                return;
            }

            const label = btn.textContent;
            btn.textContent = 'Génération…';
            btn.disabled = true;

            // Capture depuis le haut de la page, après la fin des animations d'entrée
            window.scrollTo(0, 0);
            await new Promise(r => setTimeout(r, 1300));

            const bodyBg = getComputedStyle(document.body).backgroundColor;
            const bg = (bodyBg && bodyBg !== 'rgba(0, 0, 0, 0)') ? bodyBg : '#ffffff';
            const width = zone.offsetWidth;
            const height = zone.scrollHeight;

            const opt = {
                margin: 0,
                filename: 'sioka-stats-' + new Date().toISOString().slice(0, 10) + '.pdf',
                image: { type: 'jpeg', quality: 0.95 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: bg,
                    scrollX: 0,
                    scrollY: 0,
                    onclone: (doc) => {
                        doc.querySelectorAll('#export-zone svg, #export-zone svg *').forEach(el => {
                            ['stroke', 'fill', 'stop-color'].forEach(attr => {
                                const v = el.getAttribute && el.getAttribute(attr);
                                if (v && v.indexOf('var(') !== -1) el.setAttribute(attr, resolveVars(v));
                            });
                        });
                    }
                },
                // Une seule page à la hauteur du contenu : aucun bloc coupé en deux
                jsPDF: { unit: 'px', format: [width, height], orientation: height > width ? 'portrait' : 'landscape', hotfixes: ['px_scaling'] }
            };

            try {
                await html2pdf().set(opt).from(zone).save();
            } catch (e) {
                console.error(e);
                alert("L'export PDF a échoué. Ouvrez la console du navigateur pour plus de détails.");
            } finally {
                btn.textContent = label;
                btn.disabled = false;
            }
        });
    })();
</script>
@endpush