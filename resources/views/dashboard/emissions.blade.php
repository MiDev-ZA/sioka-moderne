@extends('layouts.dashboard-layout')

@section('title', "Émissions - SIOKA")

@section('content')

@php
    // Ordre logique de la semaine
    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    $programmesCollection = collect($programmes ?? []);
    $programmesParJour = $programmesCollection->groupBy('day');

    // Jour courant (heure de Madagascar) : repéré dans les onglets
    $aujourdhui = $jours[now('Indian/Antananarivo')->dayOfWeekIso - 1];

    // Sélecteurs d'heure : 24 h, heures et minutes uniquement
    $heures  = array_map(fn ($n) => str_pad($n, 2, '0', STR_PAD_LEFT), range(0, 23));
    $minutes = array_map(fn ($n) => str_pad($n, 2, '0', STR_PAD_LEFT), range(0, 59));
@endphp

<style>
    /* ==================== Formulaire ==================== */
    .programme-form .field { margin-bottom: 18px; }
    .programme-form .field:last-child { margin-bottom: 0; }

    .programme-form label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }
    .programme-form .req { color: var(--magenta); font-weight: 700; }

    .programme-form input[type="text"],
    .programme-form input[type="time"],
    .programme-form select,
    .programme-form textarea {
        width: 100%;
        padding: 10px 14px;
        font: inherit;
        font-size: 14px;
        color: inherit;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
        border-radius: 12px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .programme-form textarea { resize: vertical; line-height: 1.6; }
    .programme-form ::placeholder { color: var(--plomb); opacity: .85; }
    /* Le menu déroulant natif ne doit pas hériter d'un fond translucide */
    .programme-form select option { background: var(--ink-2); color: var(--blanc); }
    .programme-form input:focus,
    .programme-form select:focus,
    .programme-form textarea:focus {
        outline: none;
        border-color: var(--bleu);
        box-shadow: 0 0 0 3px var(--bleu-glow);
    }
    .programme-form .has-error { border-color: var(--magenta) !important; }

    .form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 18px; }
    .form-row .field { margin-bottom: 0; }

    /* Sélecteur d'heure (HH : MM, format 24 h) */
    .time-select { display: flex; align-items: center; gap: 6px; }
    .time-select select { flex: 1; min-width: 0; text-align: center; }
    .time-select__sep { font-weight: 700; color: var(--plomb); }

    .field-row { display: flex; align-items: baseline; justify-content: space-between; }
    .field-hint { margin: 6px 0 0; font-size: 12px; color: var(--plomb); }
    .field-error { margin: 6px 0 0; font-size: 12px; color: var(--magenta); font-weight: 600; }
    .char-counter { font-size: 11.5px; color: var(--plomb); }
    .char-counter.is-over { color: var(--magenta); font-weight: 700; }

    .sec-head-icon { display: flex; align-items: center; gap: 10px; }
    .sec-head-icon .stat-icon { width: 34px; height: 34px; }
    .sec-head-icon .stat-icon svg { width: 16px; height: 16px; }

    .editor-actions {
        display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;
        margin-top: 28px;
    }
    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px; font-size: 13.5px; font-weight: 600;
        border-radius: 999px; border: 1px solid transparent;
        cursor: pointer; text-decoration: none; line-height: 1.2;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, color .15s ease;
        background: none;
    }
    .btn:active { transform: translateY(1px); }
    .btn svg { width: 15px; height: 15px; }
    .btn--primary {
        background: linear-gradient(90deg, var(--magenta), var(--bleu));
        color: #fff;
        box-shadow: 0 6px 16px var(--bleu-glow);
    }
    .btn--primary:hover { box-shadow: 0 8px 22px var(--bleu-glow); }
    .btn--ghost { background: transparent; color: var(--plomb); }
    .btn--ghost:hover { color: inherit; }

    /* ==================== En-tête de page + bouton d'ajout ==================== */
    .page-head-actions { display: flex; align-items: center; gap: 12px; }

    /* ==================== Filtre par jour ==================== */
    .day-tabs { display: flex; gap: 8px; overflow-x: auto; padding: 2px 0 8px; margin: 0 0 14px; scrollbar-width: thin; }
    .day-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 999px;
        border: 1px solid var(--glass-bord); background: var(--blanc-doux);
        color: inherit; font: inherit; font-size: 13px; font-weight: 600;
        white-space: nowrap; cursor: pointer;
        transition: border-color .15s ease, transform .15s ease;
    }
    .day-tab:hover { border-color: var(--bleu); transform: translateY(-1px); }
    .day-tab.is-active { background: linear-gradient(90deg, var(--magenta), var(--bleu)); border-color: transparent; color: #fff; }
    .day-tab__count {
        min-width: 20px; padding: 1px 7px; border-radius: 999px; text-align: center;
        font-size: 11px; font-weight: 700;
        border: 1px solid currentColor; opacity: .8;
    }
    .day-tab__today { width: 7px; height: 7px; border-radius: 50%; background: var(--magenta); }
    .day-tab.is-active .day-tab__today { background: #fff; }

    .badge-conflict {
        display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;
        padding: 1px 8px; border-radius: 999px;
        font-size: 11px; font-weight: 700;
        color: var(--or); border: 1px solid var(--or);
    }
    .programmes-table tr.day-empty td { text-align: center; color: var(--plomb); padding: 28px 12px; }

    /* ==================== Grille des programmes ==================== */
    .programmes-table-wrap { overflow-x: auto; }
    .programmes-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .programmes-table th {
        text-align: left;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--plomb);
        font-weight: 700;
        padding: 0 12px 10px;
        border-bottom: 1px solid var(--glass-bord);
        white-space: nowrap;
    }
    .programmes-table td {
        padding: 12px;
        border-bottom: 1px solid var(--glass-bord);
        vertical-align: middle;
    }
    .programmes-table tbody tr.programme-row { transition: background-color .15s ease; }
    .programmes-table tbody tr.programme-row:hover { background: var(--blanc-doux); }

    .programmes-table tr.day-row td {
        background: var(--blanc-doux);
        padding: 10px 12px;
    }
    .day-label { display: flex; align-items: center; gap: 8px; }
    .day-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--magenta); flex-shrink: 0; }
    .day-name { font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .day-count {
        font-size: 11.5px; font-weight: 600; color: var(--plomb);
        padding: 2px 8px; border-radius: 999px;
        border: 1px solid var(--glass-bord);
    }

    .time-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 10px; border-radius: 8px;
        font-size: 12.5px; font-weight: 600; white-space: nowrap;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
    }
    .time-badge svg { width: 13px; height: 13px; color: var(--plomb); flex-shrink: 0; }

    .row-programme__title { font-weight: 600; line-height: 1.3; min-width: 160px; }
    .row-description { color: var(--plomb); max-width: 320px; }

    .row-actions { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
    .row-icon-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px;
        border-radius: 9px;
        border: 1px solid var(--glass-bord);
        background: var(--blanc-doux);
        color: var(--plomb);
        cursor: pointer;
        transition: border-color .15s ease, color .15s ease, background-color .15s ease;
    }
    .row-icon-btn svg { width: 15px; height: 15px; }
    .row-icon-btn:hover { color: var(--bleu); border-color: var(--bleu); }
    .row-icon-btn--danger:hover { color: var(--magenta); border-color: var(--magenta); }

    .empty-state {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        gap: 10px; padding: 48px 20px;
        color: var(--plomb);
    }
    .empty-state svg { width: 40px; height: 40px; opacity: .6; }
    .empty-state strong { color: inherit; font-size: 15px; }

    /* ==================== Modale (fond flouté) ==================== */
    .modal-overlay {
        position: fixed; inset: 0;
        display: flex; align-items: flex-start; justify-content: center;
        padding: 40px 16px;
        background: color-mix(in srgb, var(--ink-2) 60%, transparent);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        opacity: 0; visibility: hidden;
        transition: opacity .2s ease, visibility 0s linear .2s;
        z-index: 1000;
        overflow-y: auto;
    }
    .modal-overlay.is-open {
        opacity: 1; visibility: visible;
        transition: opacity .2s ease, visibility 0s linear 0s;
    }

    .modal-panel {
        width: 100%; max-width: 680px;
        transform: translateY(14px) scale(.98);
        opacity: 0;
        transition: transform .22s cubic-bezier(.2, .8, .2, 1), opacity .22s ease;
    }
    .modal-overlay.is-open .modal-panel {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    .modal-card {
        background: var(--ink-2);
        color: var(--blanc);
        border: 1px solid var(--glass-bord);
        border-radius: 20px;
        box-shadow: 0 24px 60px color-mix(in srgb, var(--ink-2) 80%, transparent);
        max-height: calc(100vh - 80px);
        display: flex; flex-direction: column;
        overflow: hidden;
    }
    .modal-card__head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--glass-bord);
        flex-shrink: 0;
    }
    .modal-card__title { font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .modal-card__close {
        width: 34px; height: 34px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--glass-bord); background: transparent; color: var(--plomb);
        cursor: pointer; transition: border-color .15s ease, color .15s ease;
        flex-shrink: 0;
    }
    .modal-card__close svg { width: 16px; height: 16px; }
    .modal-card__close:hover { color: var(--magenta); border-color: var(--magenta); }
    .modal-card__body { padding: 24px; overflow-y: auto; }

    body.modal-lock { overflow: hidden; }

    @media (max-width: 900px) {
        .modal-overlay { padding: 0; align-items: stretch; }
        .modal-panel { max-width: none; height: 100%; }
        .modal-card { height: 100%; max-height: none; border-radius: 0; }
    }
    @media (max-width: 640px) {
        .form-row { grid-template-columns: 1fr; }
    }

    /* ==================== Toast de notification ==================== */
    .toast {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 1100;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-radius: 14px;
        font-size: 13.5px;
        font-weight: 600;
        box-shadow: 0 14px 34px color-mix(in srgb, var(--ink-2) 70%, transparent);
        background: var(--ink-2);
        border: 1px solid var(--glass-bord);
        color: var(--blanc);
        transform: translateY(-12px);
        opacity: 0;
        pointer-events: none;
        transition: transform .25s ease, opacity .25s ease;
    }
    .toast.is-visible {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    .toast--success { border-color: color-mix(in srgb, var(--vert) 45%, transparent); }
    .toast--success .toast__icon { color: var(--vert); }
    .toast__icon { display: flex; flex-shrink: 0; }
    .toast__icon svg { width: 18px; height: 18px; }
    .toast__close {
        margin-left: 6px;
        border: none; background: none; color: var(--plomb);
        cursor: pointer; display: inline-flex; padding: 0;
    }
    .toast__close svg { width: 14px; height: 14px; }

    @media (max-width: 640px) {
        .toast { left: 16px; right: 16px; top: 16px; }
    }
</style>

{{-- ===================== Toast de notification (succès) ===================== --}}
@if(session('success'))
    <div class="toast toast--success" id="pageToast">
        <span class="toast__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
        </span>
        <span>{{ session('success') }}</span>
        <button type="button" class="toast__close" id="pageToastClose" aria-label="Fermer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
@endif

{{-- ===================== En-tête, dans le style de la vue d'ensemble ===================== --}}
<div class="welcome">
    <div>
        <h1>Émissions <span>📻</span></h1>
        <p>Gérez les émissions et le planning de la semaine.</p>
    </div>
    <div class="page-head-actions">
        <button type="button" class="btn btn--primary" id="openCreateModalBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Nouveau programme
        </button>
    </div>
</div>

{{-- ===================== Grille des programmes ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="dash-sec-head">
            <div class="sec-head-icon">
                <div class="stat-icon" style="--stat-color:var(--or);">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4M8 2v4M3 10h18" />
                    </svg>
                </div>
                <div class="dash-sec-title">Programmes enregistrés ({{ $programmesCollection->count() }})</div>
            </div>
        </div>

        @if($programmesCollection->isNotEmpty())
            {{-- Filtre par jour : Lun. … Dim. avec compteurs (le jour courant est ouvert par défaut) --}}
            <div class="day-tabs" role="tablist" aria-label="Filtrer les émissions par jour">
                @foreach($jours as $jour)
                    <button type="button" class="day-tab" data-filter="{{ $jour }}" role="tab" aria-selected="false" title="{{ $jour }}">
                        {{ mb_substr($jour, 0, 3) }}.
                        @if($jour === $aujourdhui)<span class="day-tab__today" title="Aujourd'hui"></span>@endif
                        <span class="day-tab__count">{{ ($programmesParJour[$jour] ?? collect())->count() }}</span>
                    </button>
                @endforeach
            </div>

            <div class="programmes-table-wrap">
                <table class="programmes-table">
                    <thead>
                        <tr>
                            <th>Horaire</th>
                            <th>Émission</th>
                            <th>Description</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jours as $jour)
                            @php
                                // Programmes du jour, triés par heure de début
                                $programmesDuJour = ($programmesParJour[$jour] ?? collect())->sortBy('start');

                                // Détection des chevauchements (une émission qui démarre avant la fin de la précédente)
                                $conflits = [];
                                $finMax = null;
                                foreach ($programmesDuJour as $p) {
                                    $debut = substr($p->start, 0, 5);
                                    $fin   = substr($p->end, 0, 5);
                                    if ($finMax !== null && $debut < $finMax) {
                                        $conflits[$p->id] = true;
                                    }
                                    $finMax = $finMax === null ? $fin : max($finMax, $fin);
                                }
                            @endphp

                            @if($programmesDuJour->isNotEmpty())
                                <tr class="day-row" data-day="{{ $jour }}">
                                    <td colspan="4">
                                        <div class="day-label">
                                            <span class="day-dot"></span>
                                            <span class="day-name">{{ $jour }}</span>
                                            <span class="day-count">{{ $programmesDuJour->count() }} émission(s)</span>
                                        </div>
                                    </td>
                                </tr>

                                @foreach($programmesDuJour as $programme)
                                    <tr class="programme-row" data-day="{{ $jour }}">
                                        <td>
                                            <span class="time-badge">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                                {{ substr($programme->start, 0, 5) }} - {{ substr($programme->end, 0, 5) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="row-programme__title">{{ $programme->title }}</div>
                                            @if(isset($conflits[$programme->id]))
                                                <span class="badge-conflict" title="Cette émission démarre avant la fin de la précédente">⚠ Chevauchement</span>
                                            @endif
                                        </td>
                                        <td class="row-description">{{ \Illuminate\Support\Str::limit($programme->description, 60) }}</td>
                                        <td>
                                            <div class="row-actions">
                                                <button type="button"
                                                        class="row-icon-btn js-edit-programme"
                                                        title="Modifier"
                                                        data-programme="{{ json_encode([
                                                            'id' => $programme->id,
                                                            'day' => $programme->day,
                                                            'start' => substr($programme->start, 0, 5),
                                                            'end' => substr($programme->end, 0, 5),
                                                            'title' => $programme->title,
                                                            'description' => $programme->description,
                                                        ]) }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/></svg>
                                                </button>
                                                <button type="button"
                                                        class="row-icon-btn row-icon-btn--danger js-delete-programme"
                                                        title="Supprimer"
                                                        data-action="{{ route('emissions.destroy', $programme->id) }}"
                                                        data-title="{{ $programme->title }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 7"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="day-empty" data-day="{{ $jour }}" hidden>
                                    <td colspan="4">Aucune émission programmée le {{ mb_strtolower($jour) }}.</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <strong>Aucun programme pour le moment</strong>
                <p>Ajoutez votre première émission pour qu'elle apparaisse dans la grille.</p>
                <button type="button" class="btn btn--primary" id="openCreateModalBtnEmpty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                    Nouveau programme
                </button>
            </div>
        @endif
    </div>
</section>

{{-- ===================== Modale : formulaire de création / modification ===================== --}}
<div class="modal-overlay" id="programmeModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span id="modalIcon">📻</span>
                    <span id="modalTitleText">Nouveau programme</span>
                </div>
                <button type="button" class="modal-card__close" id="closeModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="{{ route('emissions.store') }}" method="POST" id="programmeForm" class="programme-form" novalidate>
                    @csrf
                    <input type="hidden" name="_method" id="methodInput" value="{{ old('_method', 'POST') }}">
                    <input type="hidden" name="id" id="programmeIdInput" value="{{ old('id') }}">

                    <section class="panel">
                        <div class="panel-pad">

                            <div class="dash-sec-head">
                                <div class="sec-head-icon">
                                    <div class="stat-icon" style="--stat-color:var(--or);">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <path d="M16 2v4M8 2v4M3 10h18" />
                                        </svg>
                                    </div>
                                    <div class="dash-sec-title">Détails du programme</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="field">
                                    <label for="day">Jour de diffusion <span class="req">*</span></label>
                                    <select name="day" id="day" class="{{ $errors->has('day') ? 'has-error' : '' }}" required>
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($jours as $jour)
                                            <option value="{{ $jour }}" @selected(old('day') === $jour)>{{ $jour }}</option>
                                        @endforeach
                                    </select>
                                    @error('day')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="field">
                                    <label for="start_h">Début <span class="req">*</span></label>
                                    <div class="time-select" data-time-target="start">
                                        <select id="start_h" class="js-time-h {{ $errors->has('start') ? 'has-error' : '' }}" aria-label="Heure de début">
                                            <option value="">HH</option>
                                            @foreach($heures as $h)
                                                <option value="{{ $h }}">{{ $h }}</option>
                                            @endforeach
                                        </select>
                                        <span class="time-select__sep">:</span>
                                        <select id="start_m" class="js-time-m {{ $errors->has('start') ? 'has-error' : '' }}" aria-label="Minutes de début">
                                            <option value="">MM</option>
                                            @foreach($minutes as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="start" id="start" value="{{ old('start') }}">
                                    @error('start')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="field">
                                    <label for="end_h">Fin <span class="req">*</span></label>
                                    <div class="time-select" data-time-target="end">
                                        <select id="end_h" class="js-time-h {{ $errors->has('end') ? 'has-error' : '' }}" aria-label="Heure de fin">
                                            <option value="">HH</option>
                                            @foreach($heures as $h)
                                                <option value="{{ $h }}">{{ $h }}</option>
                                            @endforeach
                                        </select>
                                        <span class="time-select__sep">:</span>
                                        <select id="end_m" class="js-time-m {{ $errors->has('end') ? 'has-error' : '' }}" aria-label="Minutes de fin">
                                            <option value="">MM</option>
                                            @foreach($minutes as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="end" id="end" value="{{ old('end') }}">
                                    @error('end')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="field">
                                <label for="title">Titre de l'émission <span class="req">*</span></label>
                                <input type="text" name="title" id="title" maxlength="180"
                                       value="{{ old('title') }}"
                                       class="{{ $errors->has('title') ? 'has-error' : '' }}"
                                       placeholder="Ex. : Le Grand Débat"
                                       required>
                                @error('title')<p class="field-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="field">
                                <div class="field-row">
                                    <label for="description">Description <span class="req">*</span></label>
                                    <span class="char-counter" id="descriptionCounter">0 / 200</span>
                                </div>
                                <textarea name="description" id="description" rows="3" maxlength="200"
                                          class="{{ $errors->has('description') ? 'has-error' : '' }}"
                                          placeholder="Brève description du contenu de l'émission"
                                          required>{{ old('description') }}</textarea>
                                @error('description')<p class="field-error">{{ $message }}</p>@enderror
                            </div>

                        </div>
                    </section>

                    {{-- ===================== Actions ===================== --}}
                    <div class="editor-actions">
                        <button type="button" class="btn btn--ghost" id="cancelModalBtn">Annuler</button>
                        <button type="submit" class="btn btn--primary" id="submitBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l6 6L20 6"/></svg>
                            <span id="submitBtnText">Ajouter au planning</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : confirmation de suppression ===================== --}}
<div class="modal-overlay" id="programmeDeleteModalOverlay">
    <div class="modal-panel" style="max-width: 420px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer le programme</span>
                </div>
                <button type="button" class="modal-card__close" id="closeDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p style="font-size:14px; line-height:1.6; color: var(--plomb); margin: 0 0 20px;">
                    Supprimer définitivement l'émission « <strong id="deleteProgrammeTitle" style="color: inherit;"></strong> » ? Cette action est irréversible.
                </p>
                <form action="" method="POST" id="programmeDeleteForm" style="display:flex; gap:10px;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn--ghost" id="cancelDeleteBtn" style="flex:1; justify-content:center; border: 1px solid var(--glass-bord);">
                        Annuler
                    </button>
                    <button type="submit" class="btn" style="flex:1; justify-content:center; background: var(--magenta); color: #fff;">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Script : modale (ouverture/fermeture) + formulaire de création/modification --}}
<script>
    (function () {
        const form = document.getElementById('programmeForm');
        if (!form) return;

        const storeUrl = @json(route('emissions.store'));
        const updateUrlTemplate = @json(route('emissions.update', '__ID__'));

        // ---------- Modale : ouverture / fermeture ----------
        const overlay = document.getElementById('programmeModalOverlay');
        const modalTitleText = document.getElementById('modalTitleText');
        const modalIcon = document.getElementById('modalIcon');
        const submitBtnText = document.getElementById('submitBtnText');
        const methodInput = document.getElementById('methodInput');
        const programmeIdInput = document.getElementById('programmeIdInput');

        const dayInput = document.getElementById('day');
        const startInput = document.getElementById('start');
        const endInput = document.getElementById('end');
        const titleInput = document.getElementById('title');
        const description = document.getElementById('description');
        const descriptionCounter = document.getElementById('descriptionCounter');

        // ---------- Sélecteurs d'heure (HH : MM) liés aux champs cachés start / end ----------
        function bindTime(hidden) {
            const wrap = document.querySelector('[data-time-target="' + hidden.id + '"]');
            const h = wrap.querySelector('.js-time-h');
            const m = wrap.querySelector('.js-time-m');

            function sync() {
                hidden.value = (h.value !== '' && m.value !== '') ? h.value + ':' + m.value : '';
            }
            h.addEventListener('change', function () {
                if (h.value !== '' && m.value === '') m.value = '00';
                sync();
            });
            m.addEventListener('change', function () {
                if (m.value !== '' && h.value === '') h.value = '00';
                sync();
            });

            return {
                focus: function () { h.focus(); },
                set: function (v) {
                    const parts = String(v || '').substring(0, 5).split(':');
                    h.value = parts[0] || '';
                    m.value = parts[1] || '';
                    sync();
                }
            };
        }
        const startTime = bindTime(startInput);
        const endTime = bindTime(endInput);
        startTime.set(startInput.value);   // restaure old() après une erreur de validation
        endTime.set(endInput.value);

        function lockScroll(lock) {
            document.body.classList.toggle('modal-lock', lock);
        }

        function openModal() {
            overlay.classList.add('is-open');
            lockScroll(true);
        }

        function closeModal() {
            overlay.classList.remove('is-open');
            lockScroll(false);
        }

        function updateDescriptionCounter() {
            const len = description.value.length;
            descriptionCounter.textContent = len + ' / 200';
            descriptionCounter.classList.toggle('is-over', len >= 200);
        }
        description.addEventListener('input', updateDescriptionCounter);

        function resetFormToCreateMode() {
            form.reset();
            // form.reset() restaure les old() rendus côté serveur : on vide explicitement les champs
            dayInput.value = '';
            startTime.set('');
            endTime.set('');
            titleInput.value = '';
            description.value = '';

            form.action = storeUrl;
            methodInput.value = 'POST';
            programmeIdInput.value = '';
            modalTitleText.textContent = 'Nouveau programme';
            modalIcon.textContent = '📻';
            submitBtnText.textContent = 'Ajouter au planning';
            updateDescriptionCounter();
        }

        function fillFormForEdit(data) {
            form.action = updateUrlTemplate.replace('__ID__', data.id);
            methodInput.value = 'PUT';
            programmeIdInput.value = data.id;

            dayInput.value = data.day || '';
            startTime.set(data.start);
            endTime.set(data.end);
            titleInput.value = data.title || '';
            description.value = data.description || '';
            updateDescriptionCounter();

            modalTitleText.textContent = 'Modifier le programme';
            modalIcon.textContent = '✏️';
            submitBtnText.textContent = 'Mettre à jour';
        }

        // ---------- Filtre par jour ----------
        const dayTabs = document.querySelectorAll('.day-tab');
        const dayRows = document.querySelectorAll('.programmes-table tbody tr[data-day]');
        let activeDay = @json($aujourdhui);   // jour ouvert par défaut : aujourd'hui

        function applyDayFilter(filter) {
            activeDay = filter;

            dayTabs.forEach(function (tab) {
                const on = tab.dataset.filter === filter;
                tab.classList.toggle('is-active', on);
                tab.setAttribute('aria-selected', on ? 'true' : 'false');
            });

            dayRows.forEach(function (row) {
                if (row.classList.contains('day-row')) {
                    // L'en-tête de jour est redondant avec l'onglet
                    row.hidden = true;
                } else {
                    row.hidden = row.dataset.day !== filter;
                }
            });
        }

        dayTabs.forEach(function (tab) {
            tab.addEventListener('click', function () { applyDayFilter(tab.dataset.filter); });
        });
        if (dayTabs.length) applyDayFilter(activeDay);

        // Bouton(s) d'ouverture en mode création
        ['openCreateModalBtn', 'openCreateModalBtnEmpty'].forEach(function (id) {
            const btn = document.getElementById(id);
            if (btn) btn.addEventListener('click', function () {
                resetFormToCreateMode();
                // Le jour affiché est présélectionné dans le formulaire
                dayInput.value = activeDay;
                openModal();
                dayInput.focus();
            });
        });

        // Boutons "Modifier" de chaque ligne
        document.querySelectorAll('.js-edit-programme').forEach(function (btn) {
            btn.addEventListener('click', function () {
                try {
                    const data = JSON.parse(btn.getAttribute('data-programme'));
                    fillFormForEdit(data);
                    openModal();
                } catch (e) {
                    console.error('Impossible de lire les données du programme', e);
                }
            });
        });

        // Fermeture : bouton croix, bouton "Annuler", clic hors de la carte, touche Échap
        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('cancelModalBtn').addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
        });

        // ---------- Modale : confirmation de suppression ----------
        const deleteOverlay = document.getElementById('programmeDeleteModalOverlay');
        const deleteForm = document.getElementById('programmeDeleteForm');
        const deleteProgrammeTitle = document.getElementById('deleteProgrammeTitle');
        const closeDeleteModalBtn = document.getElementById('closeDeleteModalBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

        function openDeleteModal() {
            deleteOverlay.classList.add('is-open');
            lockScroll(true);
        }
        function closeDeleteModal() {
            deleteOverlay.classList.remove('is-open');
            lockScroll(false);
        }

        document.querySelectorAll('.js-delete-programme').forEach(function (btn) {
            btn.addEventListener('click', function () {
                deleteForm.action = btn.dataset.action;
                deleteProgrammeTitle.textContent = btn.dataset.title;
                openDeleteModal();
            });
        });

        closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        deleteOverlay.addEventListener('click', function (e) {
            if (e.target === deleteOverlay) closeDeleteModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && deleteOverlay.classList.contains('is-open')) closeDeleteModal();
        });

        updateDescriptionCounter();

        // ---------- Toast de notification (auto-disparition) ----------
        const pageToast = document.getElementById('pageToast');
        if (pageToast) {
            const pageToastClose = document.getElementById('pageToastClose');
            requestAnimationFrame(function () {
                pageToast.classList.add('is-visible');
            });

            let toastTimer = setTimeout(hideToast, 5000);

            function hideToast() {
                pageToast.classList.remove('is-visible');
            }

            if (pageToastClose) {
                pageToastClose.addEventListener('click', function () {
                    clearTimeout(toastTimer);
                    hideToast();
                });
            }
        }

        // ---------- Réouverture automatique de la modale après une erreur de validation ----------
        @if($errors->any())
            (function () {
                @if(old('_method') === 'PUT' && old('id'))
                    modalTitleText.textContent = 'Modifier le programme';
                    modalIcon.textContent = '✏️';
                    submitBtnText.textContent = 'Mettre à jour';
                    form.action = updateUrlTemplate.replace('__ID__', @json(old('id')));
                @else
                    form.action = storeUrl;
                @endif
                openModal();
            })();
        @endif
    })();
</script>
@endpush