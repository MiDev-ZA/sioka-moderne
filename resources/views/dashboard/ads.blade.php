@extends('layouts.dashboard-layout')

@section('title', "Publicités - SIOKA")

@section('content')

@php
    // Emplacements disponibles : doivent correspondre à la règle "in:" de AdController::validated()
    $slots = [
        'top-ticker'          => 'Haut du site (au-dessus du ticker)',
        'home-after-news'     => 'Accueil : après les actualités',
        'home-after-podcasts' => 'Accueil : après les podcasts',
    ];

    $adsCollection = collect($ads ?? []);

    // Statut calculé avec la même logique que le scope Ad::displayable()
    $statutDe = function ($ad) {
        if (! $ad->is_active) return ['off', 'Désactivée'];
        if ($ad->starts_at && $ad->starts_at->gt(today())) return ['soon', 'Programmée'];
        if ($ad->ends_at && $ad->ends_at->lt(today())) return ['end', 'Expirée'];
        return ['on', 'En ligne'];
    };
@endphp

<style>
    /* ==================== Formulaire ==================== */
    .ad-form .field { margin-bottom: 18px; }
    .ad-form .field:last-child { margin-bottom: 0; }

    .ad-form label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }
    .ad-form .req { color: var(--magenta); font-weight: 700; }

    .ad-form input[type="text"],
    .ad-form input[type="url"],
    .ad-form input[type="date"],
    .ad-form input[type="file"],
    .ad-form select {
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
    .ad-form ::placeholder { color: var(--plomb); opacity: .85; }
    /* Le menu déroulant natif ne doit pas hériter d'un fond translucide */
    .ad-form select option { background: var(--ink-2); color: var(--blanc); }
    .ad-form input:focus,
    .ad-form select:focus {
        outline: none;
        border-color: var(--bleu);
        box-shadow: 0 0 0 3px var(--bleu-glow);
    }
    .ad-form .has-error { border-color: var(--magenta) !important; }

    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-bottom: 18px; }
    .form-row .field { margin-bottom: 0; }

    .field-hint { margin: 6px 0 0; font-size: 12px; color: var(--plomb); }
    .field-error { margin: 6px 0 0; font-size: 12px; color: var(--magenta); font-weight: 600; }

    .check-field { display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0 !important; }
    .check-field input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--bleu); cursor: pointer; }

    /* Aperçu de l'image dans le formulaire */
    .ad-preview {
        margin-top: 10px;
        padding: 8px;
        border-radius: 12px;
        border: 1px solid var(--glass-bord);
        background: var(--blanc-doux);
    }
    .ad-preview img { width: 100%; max-height: 120px; object-fit: contain; border-radius: var(--r-sm); }

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
        color: var(--fixe-clair);
        box-shadow: 0 6px 16px var(--bleu-glow);
    }
    .btn--primary:hover { box-shadow: 0 8px 22px var(--bleu-glow); }
    .btn--ghost { background: transparent; color: var(--plomb); }
    .btn--ghost:hover { color: inherit; }
    .btn--danger { background: var(--magenta); color: var(--fixe-clair); }

    /* ==================== En-tête de page ==================== */
    .page-head-actions { display: flex; align-items: center; gap: 12px; }

    .ads-notice {
        display: flex; align-items: flex-start; gap: 10px;
        margin-bottom: 18px; padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid var(--or);
        color: var(--or);
        font-size: 13px; font-weight: 600; line-height: 1.5;
    }

    /* ==================== Liste des publicités ==================== */
    .ads-table-wrap { overflow-x: auto; }
    .ads-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ads-table th {
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
    .ads-table td {
        padding: 12px;
        border-bottom: 1px solid var(--glass-bord);
        vertical-align: middle;
    }
    .ads-table tbody tr { transition: background-color .15s ease; }
    .ads-table tbody tr:hover { background: var(--blanc-doux); }

    .ad-thumb {
        width: 120px; height: 44px;
        object-fit: contain;
        border-radius: var(--r-sm);
        border: 1px solid var(--glass-bord);
        background: var(--blanc-doux);
    }
    .row-ad__title { font-weight: 600; line-height: 1.3; min-width: 140px; }
    .row-ad__sub { color: var(--plomb); font-size: 12px; margin-top: 2px; }
    .row-slot { color: var(--plomb); min-width: 150px; }
    .row-period { white-space: nowrap; font-size: 12.5px; }
    .row-stats { white-space: nowrap; font-size: 12.5px; line-height: 1.5; }
    .row-stats span { color: var(--plomb); }

    .ad-status {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 2px 10px; border-radius: 999px;
        font-size: 11.5px; font-weight: 700; white-space: nowrap;
        border: 1px solid currentColor;
    }
    .ad-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ad-status--on   { color: var(--vert); }
    .ad-status--off  { color: var(--plomb); }
    .ad-status--soon { color: var(--bleu); }
    .ad-status--end  { color: var(--or); }

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
    .modal-panel--sm { max-width: 420px; }
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

    .delete-text { font-size: 14px; line-height: 1.6; color: var(--plomb); margin: 0 0 20px; }
    .delete-text strong { color: inherit; }
    .delete-actions { display: flex; gap: 10px; }
    .delete-actions .btn { flex: 1; justify-content: center; }
    .delete-actions .btn--ghost { border: 1px solid var(--glass-bord); }

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
        <h1>Publicités <span>📢</span></h1>
        <p>Gérez les bannières publicitaires affichées sur le site.</p>
    </div>
    <div class="page-head-actions">
        <button type="button" class="btn btn--primary" id="openCreateModalBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Nouvelle publicité
        </button>
    </div>
</div>

@unless(config('ads.enabled'))
    <div class="ads-notice" role="status">
        <span>⚠</span>
        <span>Les publicités sont désactivées sur le site (<code>ADS_ENABLED=false</code> dans le fichier .env). Elles ne s'afficheront pas tant que vous ne l'aurez pas passé à <code>true</code>.</span>
    </div>
@endunless

{{-- ===================== Liste des publicités ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="dash-sec-head">
            <div class="sec-head-icon">
                <div class="stat-icon" style="--stat-color:var(--or);">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M3 9h18" />
                    </svg>
                </div>
                <div class="dash-sec-title">Publicités enregistrées ({{ $adsCollection->count() }})</div>
            </div>
        </div>

        @if($adsCollection->isNotEmpty())
            <div class="ads-table-wrap">
                <table class="ads-table">
                    <thead>
                        <tr>
                            <th>Aperçu</th>
                            <th>Publicité</th>
                            <th>Emplacement</th>
                            <th>Période</th>
                            <th>Statistiques</th>
                            <th>Statut</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adsCollection as $ad)
                            @php
                                [$statutClasse, $statutLibelle] = $statutDe($ad);
                                $ctr = $ad->impressions > 0 ? round($ad->clicks / $ad->impressions * 100, 2) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <img class="ad-thumb" src="{{ asset('storage/' . $ad->image) }}" alt="{{ $ad->image_alt ?? $ad->name }}" loading="lazy">
                                </td>
                                <td>
                                    <div class="row-ad__title">{{ $ad->name }}</div>
                                    @if($ad->advertiser)
                                        <div class="row-ad__sub">{{ $ad->advertiser }}</div>
                                    @endif
                                </td>
                                <td class="row-slot">{{ $slots[$ad->slot] ?? $ad->slot }}</td>
                                <td class="row-period">
                                    @if($ad->starts_at || $ad->ends_at)
                                        {{ $ad->starts_at?->format('d/m/Y') ?? '…' }} → {{ $ad->ends_at?->format('d/m/Y') ?? 'sans fin' }}
                                    @else
                                        Illimitée
                                    @endif
                                </td>
                                <td class="row-stats">
                                    {{ number_format($ad->impressions, 0, ',', ' ') }} <span>impressions</span><br>
                                    {{ number_format($ad->clicks, 0, ',', ' ') }} <span>clics ({{ str_replace('.', ',', $ctr) }} %)</span>
                                </td>
                                <td><span class="ad-status ad-status--{{ $statutClasse }}">{{ $statutLibelle }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button"
                                                class="row-icon-btn js-edit-ad"
                                                title="Modifier"
                                                data-ad="{{ json_encode([
                                                    'id' => $ad->id,
                                                    'name' => $ad->name,
                                                    'advertiser' => $ad->advertiser,
                                                    'slot' => $ad->slot,
                                                    'image_url' => asset('storage/' . $ad->image),
                                                    'image_alt' => $ad->image_alt,
                                                    'url' => $ad->url,
                                                    'starts_at' => $ad->starts_at?->format('Y-m-d'),
                                                    'ends_at' => $ad->ends_at?->format('Y-m-d'),
                                                    'is_active' => $ad->is_active,
                                                ]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/></svg>
                                        </button>
                                        <button type="button"
                                                class="row-icon-btn row-icon-btn--danger js-delete-ad"
                                                title="Supprimer"
                                                data-action="{{ route('ads.destroy', $ad->id) }}"
                                                data-title="{{ $ad->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 7"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/></svg>
                <strong>Aucune publicité pour le moment</strong>
                <p>Ajoutez votre première bannière pour qu'elle s'affiche sur le site.</p>
            </div>
        @endif
    </div>
</section>

{{-- ===================== Modale : formulaire de création / modification ===================== --}}
<div class="modal-overlay" id="adModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span id="modalIcon">📢</span>
                    <span id="modalTitleText">Nouvelle publicité</span>
                </div>
                <button type="button" class="modal-card__close" id="closeModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="{{ route('ads.store') }}" method="POST" id="adForm" class="ad-form" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="_method" id="methodInput" value="{{ old('_method', 'POST') }}">
                    <input type="hidden" name="id" id="adIdInput" value="{{ old('id') }}">

                    <section class="panel">
                        <div class="panel-pad">

                            <div class="dash-sec-head">
                                <div class="sec-head-icon">
                                    <div class="stat-icon" style="--stat-color:var(--or);">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="5" width="18" height="14" rx="2" />
                                            <path d="M3 9h18" />
                                        </svg>
                                    </div>
                                    <div class="dash-sec-title">Détails de la publicité</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="field">
                                    <label for="name">Nom de la campagne <span class="req">*</span></label>
                                    <input type="text" name="name" id="name" maxlength="120"
                                           value="{{ old('name') }}"
                                           class="{{ $errors->has('name') ? 'has-error' : '' }}"
                                           placeholder="Ex. : Bannière Telma - Décembre"
                                           required>
                                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="field">
                                    <label for="advertiser">Annonceur</label>
                                    <input type="text" name="advertiser" id="advertiser" maxlength="120"
                                           value="{{ old('advertiser') }}"
                                           class="{{ $errors->has('advertiser') ? 'has-error' : '' }}"
                                           placeholder="Ex. : Telma">
                                    @error('advertiser')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="field">
                                <label for="slot">Emplacement <span class="req">*</span></label>
                                <select name="slot" id="slot" class="{{ $errors->has('slot') ? 'has-error' : '' }}" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($slots as $valeur => $libelle)
                                        <option value="{{ $valeur }}" @selected(old('slot') === $valeur)>{{ $libelle }}</option>
                                    @endforeach
                                </select>
                                @error('slot')<p class="field-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="field">
                                <label for="image">Image de la bannière <span class="req" id="imageRequiredMark">*</span></label>
                                <input type="file" name="image" id="image" accept="image/*"
                                       class="{{ $errors->has('image') ? 'has-error' : '' }}">
                                <p class="field-hint" id="imageHint">JPG, PNG, WebP ou GIF, 2 Mo maximum. Une bannière très large (ex. 1920 × 180 px) convient aux emplacements du site.</p>
                                @error('image')<p class="field-error">{{ $message }}</p>@enderror
                                <div class="ad-preview" id="imagePreviewWrap" hidden>
                                    <img id="imagePreview" alt="Aperçu de la bannière">
                                </div>
                            </div>

                            <div class="field">
                                <label for="image_alt">Texte alternatif de l'image</label>
                                <input type="text" name="image_alt" id="image_alt" maxlength="160"
                                       value="{{ old('image_alt') }}"
                                       class="{{ $errors->has('image_alt') ? 'has-error' : '' }}"
                                       placeholder="Description courte de la bannière">
                                @error('image_alt')<p class="field-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="field">
                                <label for="url">Lien de destination <span class="req">*</span></label>
                                <input type="url" name="url" id="url" maxlength="500"
                                       value="{{ old('url') }}"
                                       class="{{ $errors->has('url') ? 'has-error' : '' }}"
                                       placeholder="https://www.exemple.mg"
                                       required>
                                @error('url')<p class="field-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="form-row">
                                <div class="field">
                                    <label for="starts_at">Début de diffusion</label>
                                    <input type="date" name="starts_at" id="starts_at"
                                           value="{{ old('starts_at') }}"
                                           class="{{ $errors->has('starts_at') ? 'has-error' : '' }}">
                                    @error('starts_at')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="field">
                                    <label for="ends_at">Fin de diffusion</label>
                                    <input type="date" name="ends_at" id="ends_at"
                                           value="{{ old('ends_at') }}"
                                           class="{{ $errors->has('ends_at') ? 'has-error' : '' }}">
                                    @error('ends_at')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <p class="field-hint" style="margin: -6px 0 18px;">Laissez les dates vides pour une diffusion sans limite de durée.</p>

                            <div class="field">
                                <label class="check-field" for="is_active">
                                    <input type="checkbox" name="is_active" id="is_active" value="1">
                                    <span>Publicité active (visible sur le site)</span>
                                </label>
                            </div>

                        </div>
                    </section>

                    {{-- ===================== Actions ===================== --}}
                    <div class="editor-actions">
                        <button type="button" class="btn btn--ghost" id="cancelModalBtn">Annuler</button>
                        <button type="submit" class="btn btn--primary" id="submitBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l6 6L20 6"/></svg>
                            <span id="submitBtnText">Ajouter la publicité</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : confirmation de suppression ===================== --}}
<div class="modal-overlay" id="adDeleteModalOverlay">
    <div class="modal-panel modal-panel--sm">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer la publicité</span>
                </div>
                <button type="button" class="modal-card__close" id="closeDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p class="delete-text">
                    Supprimer définitivement la publicité « <strong id="deleteAdTitle"></strong> » ? Son image et ses statistiques seront perdues. Cette action est irréversible.
                </p>
                <form action="" method="POST" id="adDeleteForm" class="delete-actions">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn--ghost" id="cancelDeleteBtn">Annuler</button>
                    <button type="submit" class="btn btn--danger">Supprimer</button>
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
        const form = document.getElementById('adForm');
        if (!form) return;

        const storeUrl = @json(route('ads.store'));
        const updateUrlTemplate = @json(route('ads.update', '__ID__'));

        // ---------- Éléments ----------
        const overlay = document.getElementById('adModalOverlay');
        const modalTitleText = document.getElementById('modalTitleText');
        const modalIcon = document.getElementById('modalIcon');
        const submitBtnText = document.getElementById('submitBtnText');
        const methodInput = document.getElementById('methodInput');
        const adIdInput = document.getElementById('adIdInput');

        const nameInput = document.getElementById('name');
        const advertiserInput = document.getElementById('advertiser');
        const slotInput = document.getElementById('slot');
        const imageInput = document.getElementById('image');
        const imageAltInput = document.getElementById('image_alt');
        const urlInput = document.getElementById('url');
        const startsAtInput = document.getElementById('starts_at');
        const endsAtInput = document.getElementById('ends_at');
        const isActiveInput = document.getElementById('is_active');

        const imageRequiredMark = document.getElementById('imageRequiredMark');
        const imageHint = document.getElementById('imageHint');
        const imagePreviewWrap = document.getElementById('imagePreviewWrap');
        const imagePreview = document.getElementById('imagePreview');

        const hintCreate = imageHint.textContent;
        const hintEdit = "Laissez vide pour conserver l'image actuelle. " + hintCreate;

        // Image actuellement enregistrée (mode modification)
        let currentImageUrl = '';

        function showPreview(src) {
            if (src) {
                imagePreview.src = src;
                imagePreviewWrap.hidden = false;
            } else {
                imagePreview.removeAttribute('src');
                imagePreviewWrap.hidden = true;
            }
        }

        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];
            showPreview(file ? URL.createObjectURL(file) : currentImageUrl);
        });

        // ---------- Modale : ouverture / fermeture ----------
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

        function resetFormToCreateMode() {
            form.reset();
            // form.reset() restaure les old() rendus côté serveur : on vide explicitement les champs
            nameInput.value = '';
            advertiserInput.value = '';
            slotInput.value = '';
            imageInput.value = '';
            imageAltInput.value = '';
            urlInput.value = '';
            startsAtInput.value = '';
            endsAtInput.value = '';
            isActiveInput.checked = true;

            currentImageUrl = '';
            showPreview('');
            imageRequiredMark.hidden = false;
            imageHint.textContent = hintCreate;

            form.action = storeUrl;
            methodInput.value = 'POST';
            adIdInput.value = '';
            modalTitleText.textContent = 'Nouvelle publicité';
            modalIcon.textContent = '📢';
            submitBtnText.textContent = 'Ajouter la publicité';
        }

        function fillFormForEdit(data) {
            form.action = updateUrlTemplate.replace('__ID__', data.id);
            methodInput.value = 'PUT';
            adIdInput.value = data.id;

            nameInput.value = data.name || '';
            advertiserInput.value = data.advertiser || '';
            slotInput.value = data.slot || '';
            imageInput.value = '';
            imageAltInput.value = data.image_alt || '';
            urlInput.value = data.url || '';
            startsAtInput.value = data.starts_at || '';
            endsAtInput.value = data.ends_at || '';
            isActiveInput.checked = !!data.is_active;

            currentImageUrl = data.image_url || '';
            showPreview(currentImageUrl);
            imageRequiredMark.hidden = true;
            imageHint.textContent = hintEdit;

            modalTitleText.textContent = 'Modifier la publicité';
            modalIcon.textContent = '✏️';
            submitBtnText.textContent = 'Mettre à jour';
        }

        // Bouton d'ouverture en mode création (en haut à droite)
        const openCreateBtn = document.getElementById('openCreateModalBtn');
        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                resetFormToCreateMode();
                openModal();
                nameInput.focus();
            });
        }

        // Boutons "Modifier" de chaque ligne
        document.querySelectorAll('.js-edit-ad').forEach(function (btn) {
            btn.addEventListener('click', function () {
                try {
                    const data = JSON.parse(btn.getAttribute('data-ad'));
                    fillFormForEdit(data);
                    openModal();
                } catch (e) {
                    console.error('Impossible de lire les données de la publicité', e);
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
        const deleteOverlay = document.getElementById('adDeleteModalOverlay');
        const deleteForm = document.getElementById('adDeleteForm');
        const deleteAdTitle = document.getElementById('deleteAdTitle');
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

        document.querySelectorAll('.js-delete-ad').forEach(function (btn) {
            btn.addEventListener('click', function () {
                deleteForm.action = btn.dataset.action;
                deleteAdTitle.textContent = btn.dataset.title;
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
                isActiveInput.checked = @json((bool) old('is_active'));
                @if(old('_method') === 'PUT' && old('id'))
                    modalTitleText.textContent = 'Modifier la publicité';
                    modalIcon.textContent = '✏️';
                    submitBtnText.textContent = 'Mettre à jour';
                    imageRequiredMark.hidden = true;
                    imageHint.textContent = hintEdit;
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