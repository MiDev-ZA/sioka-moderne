@extends('layouts.app')

@section('title', 'Grille des Programmes - SIOKA')

@section('content')

@php
    // "HH:MM:SS" -> minutes depuis minuit
    $enMinutes = function ($time) {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    };

    // Émission qui suit immédiatement celle à l'antenne (ou la prochaine du jour s'il n'y a rien en cours)
    $suivantId = ($programmes[$jourActuel] ?? collect())
        ->first(fn ($p) => $enMinutes($p->start) > $nowMinutes)?->id;
@endphp

<style>
    /* Espace sous le header */
    .prog-public { max-width: 960px; margin: 0 auto; padding: 56px 16px 56px; }
    @media (max-width: 640px) { .prog-public { padding-top: 36px; } }

    /* ---- En-tête : sur-titre + titre ---- */
    .prog-eyebrow {
        margin: 0 0 6px; color: var(--magenta);
        font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
    }

    /* Les éléments masqués par le script doivent vraiment disparaître (même avec display:grid) */
    .prog-public [hidden] { display: none !important; }

    /* ---- Recherche ---- */
    .prog-search { position: relative; margin: 18px 0 0; }
    .prog-search__icon {
        position: absolute; left: 16px; top: 50%; width: 17px; height: 17px;
        transform: translateY(-50%); color: var(--plomb); pointer-events: none;
    }
    .prog-search input {
        width: 100%; padding: 12px 44px 12px 44px;
        font: inherit; font-size: 14px; color: inherit;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord); border-radius: 999px;
        transition: border-color .15s ease, box-shadow .15s ease;
        -webkit-appearance: none; appearance: none;
    }
    .prog-search input::placeholder { color: var(--plomb); opacity: .85; }
    .prog-search input::-webkit-search-cancel-button { display: none; }
    .prog-search input:focus {
        outline: none; border-color: var(--bleu);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--bleu) 25%, transparent);
    }
    .prog-search__clear {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        width: 28px; height: 28px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        border: none; background: transparent; color: var(--plomb); cursor: pointer;
        transition: color .15s ease;
    }
    .prog-search__clear:hover { color: var(--magenta); }
    .prog-search__clear svg { width: 15px; height: 15px; }
    .prog-search__status { margin: 12px 4px 0; color: var(--plomb); font-size: 13px; }

    /* Titre de jour (affiché uniquement pendant une recherche) */
    .prog-day-heading {
        margin: 10px 0 0; color: var(--magenta);
        font-size: 12.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    }

    /* ---- Jours : Lun. Mar. Mer. Jeu. Ven. Sam. Dim. ---- */
    .prog-tabs {
        display: flex; gap: 8px; overflow-x: auto;
        padding: 2px 0 8px; margin: 18px 0;
        scrollbar-width: thin;
    }
    .prog-tab {
        padding: 9px 18px; border-radius: 999px;
        border: 1px solid var(--glass-bord);
        background: var(--blanc-doux);
        color: inherit; font: inherit; font-size: 13.5px; font-weight: 600;
        white-space: nowrap; cursor: pointer;
        transition: border-color .15s ease, transform .15s ease;
    }
    .prog-tab:hover { border-color: var(--bleu); transform: translateY(-1px); }
    .prog-tab.is-active {
        background: linear-gradient(90deg, var(--magenta), var(--bleu));
        border-color: transparent;
        color: #fff;
    }

    /* ---- Une émission : heures à gauche, contenu à droite ---- */
    .prog-list { display: grid; gap: 12px; }
    .prog-item {
        display: grid; grid-template-columns: 84px 1fr; gap: 18px;
        padding: 16px 18px; border-radius: 16px;
        border: 1px solid var(--glass-bord);
        background: var(--blanc-doux);
        animation: progFadeUp .45s ease both;
        animation-delay: calc(var(--i, 0) * 90ms);
        transition: transform .18s ease, border-color .18s ease;
    }
    .prog-item:hover { transform: translateY(-2px); border-color: var(--bleu); }

    /* En cours : bordure magenta + lueur qui respire */
    .prog-item.is-live {
        border-color: var(--magenta);
        animation: progFadeUp .45s ease both, progGlow 2.4s ease-in-out infinite;
        animation-delay: calc(var(--i, 0) * 90ms), 0s;
    }
    /* À suivre : bordure bleue discrète */
    .prog-item.is-next { border-color: var(--bleu); }

    .prog-time { display: flex; flex-direction: column; gap: 2px; font-size: 15px; font-weight: 700; line-height: 1.3; }
    .prog-time span:last-child { color: var(--plomb); font-weight: 500; font-size: 13.5px; }

    .prog-label {
        margin: 0 0 4px; color: var(--plomb);
        font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase;
    }
    .prog-title {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        margin: 0; font-size: 15.5px; font-weight: 700; line-height: 1.35; text-transform: uppercase;
    }
    .prog-desc { margin: 6px 0 0; color: var(--plomb); font-size: 14px; line-height: 1.6; }

    /* Étiquettes : En Direct / À suivre */
    .prog-live, .prog-next {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 3px 10px; border-radius: 999px;
        font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    }
    .prog-live { background: var(--magenta); color: #fff; animation: progBadge 1.6s ease-in-out infinite; }
    .prog-next {
        border: 1px solid var(--bleu); color: var(--bleu);
        background: transparent; animation: progBadge 2.4s ease-in-out infinite;
    }
    .prog-next svg { width: 12px; height: 12px; animation: progArrow 1.2s ease-in-out infinite; }

    /* Mini égaliseur dans l'étiquette En Direct */
    .prog-eq { display: inline-flex; align-items: flex-end; gap: 2px; height: 11px; }
    .prog-eq b {
        display: block; width: 2.5px; height: 100%; border-radius: 1px; background: #fff;
        transform-origin: bottom; animation: progEq .9s ease-in-out infinite;
    }
    .prog-eq b:nth-child(2) { animation-delay: .2s; }
    .prog-eq b:nth-child(3) { animation-delay: .4s; }

    .prog-empty {
        padding: 40px 20px; text-align: center; color: var(--plomb);
        border: 1px dashed var(--glass-bord); border-radius: 16px;
        animation: progFadeUp .45s ease both;
    }

    /* ---- Animations ---- */
    @keyframes progFadeUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes progGlow   { 0%, 100% { box-shadow: 0 0 0 0 transparent; } 50% { box-shadow: 0 0 22px color-mix(in srgb, var(--magenta) 40%, transparent); } }
    @keyframes progBadge  { 0%, 100% { opacity: 1; } 50% { opacity: .55; } }
    @keyframes progArrow  { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(3px); } }
    @keyframes progEq     { 0%, 100% { transform: scaleY(.3); } 50% { transform: scaleY(1); } }

    /* Réglage "réduire les animations" du système : on retire seulement les mouvements */
    @media (prefers-reduced-motion: reduce) {
        .prog-item, .prog-empty { animation-name: none !important; }
        .prog-item.is-live { animation-name: progGlow !important; }
        .prog-next svg { animation: none !important; }
        .prog-eq b { animation: none !important; transform: scaleY(.7); }
        .prog-tab:hover, .prog-item:hover { transform: none; }
    }

    @media (max-width: 640px) {
        .prog-item { grid-template-columns: 1fr; gap: 8px; padding: 14px 16px; }
        .prog-time { flex-direction: row; gap: 6px; }
        .prog-time span:last-child::before { content: '– '; }
    }
</style>

<section class="sec prog-public" id="programmes"
         data-now="{{ $nowMinutes }}"
         data-live="{{ $enDirectId }}"
         data-next="{{ $suivantId }}">

    <div class="sec-head">
        <div>
            <h1 class="sec-title">Grille des Programmes</h1>
        </div>
    </div>

    {{-- Recherche --}}
    <div class="prog-search">
        <svg class="prog-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="search" id="progSearch" placeholder="Rechercher une émission…" autocomplete="off" aria-label="Rechercher une émission">
        <button type="button" class="prog-search__clear" id="progSearchClear" aria-label="Effacer la recherche" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
    <p class="prog-search__status" id="progSearchStatus" role="status" aria-live="polite" hidden></p>

    {{-- Jours --}}
    <div class="prog-tabs" role="tablist" aria-label="Jours de la semaine">
        @foreach($jours as $i => $jour)
            <button type="button"
                    class="prog-tab {{ $jour === $jourActuel ? 'is-active' : '' }}"
                    role="tab"
                    id="progTab{{ $i }}"
                    title="{{ $jour }}"
                    data-target="progPanel{{ $i }}"
                    aria-selected="{{ $jour === $jourActuel ? 'true' : 'false' }}"
                    aria-controls="progPanel{{ $i }}">
                {{ mb_substr($jour, 0, 3) }}.
            </button>
        @endforeach
    </div>

    {{-- Émissions de chaque jour --}}
    @foreach($jours as $i => $jour)
        @php $duJour = $programmes[$jour] ?? collect(); @endphp

        <div class="prog-list"
             role="tabpanel"
             id="progPanel{{ $i }}"
             aria-labelledby="progTab{{ $i }}"
             @if($jour !== $jourActuel) hidden @endif>

            <h3 class="prog-day-heading" hidden>{{ $jour }}</h3>

            @forelse($duJour as $k => $programme)
                @php
                    $estEnDirect = $jour === $jourActuel && $programme->id === $enDirectId;
                    $estSuivant  = $jour === $jourActuel && $programme->id === $suivantId;
                @endphp

                <article class="prog-item {{ $estEnDirect ? 'is-live' : '' }} {{ $estSuivant ? 'is-next' : '' }}"
                         style="--i: {{ $k }};"
                         data-search="{{ $programme->title }} {{ $programme->description }}"
                         @if($jour === $jourActuel)
                             data-today="1"
                             data-id="{{ $programme->id }}"
                             data-start="{{ $enMinutes($programme->start) }}"
                             data-end="{{ $enMinutes($programme->end) }}"
                         @endif>
                    <div class="prog-time">
                        <span>{{ substr($programme->start, 0, 5) }}</span>
                        <span>{{ substr($programme->end, 0, 5) }}</span>
                    </div>
                    <div>
                        <p class="prog-label">Titre</p>
                        <h4 class="prog-title">
                            {{ $programme->title }}
                            @if($estEnDirect)
                                <span class="prog-live">
                                    <span class="prog-eq" aria-hidden="true"><b></b><b></b><b></b></span>
                                    Encours
                                </span>
                            @elseif($estSuivant)
                                <span class="prog-next">
                                    À suivre
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                                </span>
                            @endif
                        </h4>
                        <p class="prog-desc">{{ $programme->description }}</p>
                    </div>
                </article>
            @empty
                <div class="prog-empty">Aucune émission programmée ce jour-là.</div>
            @endforelse
        </div>
    @endforeach
</section>

@endsection

@push('scripts')
<script>
    (function () {
        // ---------- Onglets des jours ----------
        const tabs = document.querySelectorAll('.prog-tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) {
                    const active = t === tab;
                    t.classList.toggle('is-active', active);
                    t.setAttribute('aria-selected', active ? 'true' : 'false');
                    document.getElementById(t.dataset.target).hidden = !active;
                });
            });
        });

        // ---------- Recherche (titre + description, sans tenir compte des accents ni de la casse) ----------
        const searchInput = document.getElementById('progSearch');
        const clearBtn = document.getElementById('progSearchClear');
        const statusEl = document.getElementById('progSearchStatus');
        const tabsWrap = document.querySelector('.prog-tabs');
        const panels = document.querySelectorAll('.prog-list');
        const allItems = document.querySelectorAll('.prog-item');

        const norm = function (str) {
            return String(str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        };
        const index = new Map();
        allItems.forEach(function (item) { index.set(item, norm(item.dataset.search)); });

        function runSearch() {
            const raw = searchInput.value.trim();
            const q = norm(raw);
            clearBtn.hidden = raw === '';

            if (q === '') {
                // Retour à l'affichage normal : onglets + jour sélectionné
                const activeTab = document.querySelector('.prog-tab.is-active');
                const target = activeTab ? activeTab.dataset.target : null;
                tabsWrap.hidden = false;
                statusEl.hidden = true;
                allItems.forEach(function (item) { item.hidden = false; });
                panels.forEach(function (panel) {
                    panel.hidden = panel.id !== target;
                    panel.querySelector('.prog-day-heading').hidden = true;
                    const empty = panel.querySelector('.prog-empty');
                    if (empty) empty.hidden = false;
                });
                return;
            }

            // Recherche sur toute la semaine : on masque les onglets et on groupe par jour
            const tokens = q.split(/\s+/);
            let total = 0;
            tabsWrap.hidden = true;

            panels.forEach(function (panel) {
                let count = 0;
                panel.querySelectorAll('.prog-item').forEach(function (item) {
                    const match = tokens.every(function (t) { return index.get(item).indexOf(t) !== -1; });
                    item.hidden = !match;
                    if (match) count++;
                });
                const empty = panel.querySelector('.prog-empty');
                if (empty) empty.hidden = true;
                panel.querySelector('.prog-day-heading').hidden = false;
                panel.hidden = count === 0;
                total += count;
            });

            statusEl.hidden = false;
            statusEl.textContent = total > 0
                ? total + ' résultat' + (total > 1 ? 's' : '')
                : 'Aucune émission ne correspond à « ' + raw + ' ».';
        }

        searchInput.addEventListener('input', runSearch);
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            runSearch();
            searchInput.focus();
        });
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && searchInput.value !== '') {
                searchInput.value = '';
                runSearch();
            }
        });

        // Le rechargement automatique (changement d'émission en cours) conserve la recherche
        function reloadKeepSearch() {
            try { sessionStorage.setItem('progQ', searchInput.value); } catch (e) {}
            location.reload();
        }
        try {
            const saved = sessionStorage.getItem('progQ');
            if (saved) {
                sessionStorage.removeItem('progQ');
                searchInput.value = saved;
                runSearch();
            }
        } catch (e) {}

        // ---------- Mise à jour automatique des étiquettes "En Direct" / "À suivre" ----------
        // L'heure de référence est celle du serveur (fuseau de config/app.php) :
        // on avance à partir de l'heure reçue au chargement de la page.
        const root = document.getElementById('programmes');
        if (!root) return;

        const base = parseFloat(root.dataset.now);
        const liveId = root.dataset.live || '';
        const nextId = root.dataset.next || '';
        const t0 = Date.now();
        const items = document.querySelectorAll('.prog-item[data-today="1"]'); // déjà triés par heure

        function check() {
            const now = base + (Date.now() - t0) / 60000;

            // Passage à minuit : on recharge pour changer de jour
            if (now >= 1440) { reloadKeepSearch(); return; }

            let live = '', next = '';
            items.forEach(function (item) {
                const start = parseFloat(item.dataset.start);
                const end = parseFloat(item.dataset.end);
                if (now >= start && now < end) live = item.dataset.id;
                else if (!next && start > now) next = item.dataset.id;
            });

            // Si l'émission en cours ou la suivante a changé, on recharge pour mettre à jour les étiquettes
            if (live !== liveId || next !== nextId) reloadKeepSearch();
        }

        setInterval(check, 30000);
    })();
</script>
@endpush