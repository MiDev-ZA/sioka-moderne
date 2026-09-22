@extends('layouts.dashboard-layout')

@section('title', "Podcasts - SIOKA")

@section('content')

<style>
    /* ==================== Formulaire ==================== */
    .podcast-form .field { margin-bottom: 18px; }
    .podcast-form .field:last-child { margin-bottom: 0; }

    .podcast-form label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }
    .podcast-form .req { color: var(--magenta); font-weight: 700; }

    .podcast-form input[type="text"],
    .podcast-form input[type="url"],
    .podcast-form textarea {
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
    .podcast-form textarea { resize: vertical; line-height: 1.6; }
    .podcast-form input:focus,
    .podcast-form textarea:focus {
        outline: none;
        border-color: var(--bleu);
        box-shadow: 0 0 0 3px var(--bleu-glow);
    }
    .podcast-form .has-error { border-color: var(--magenta) !important; }

    .field-row { display: flex; align-items: baseline; justify-content: space-between; }
    .field-hint { margin: 6px 0 0; font-size: 12px; color: var(--plomb); }
    .field-error { margin: 6px 0 0; font-size: 12px; color: var(--magenta); font-weight: 600; }
    .char-counter { font-size: 11.5px; color: var(--plomb); }
    .char-counter.is-over { color: var(--magenta); font-weight: 700; }

    .sec-head-icon { display: flex; align-items: center; gap: 10px; }
    .sec-head-icon .stat-icon { width: 34px; height: 34px; }
    .sec-head-icon .stat-icon svg { width: 16px; height: 16px; }

    .permalink { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--plomb); flex-wrap: wrap; }
    .permalink code { color: var(--bleu); font-family: inherit; }
    .permalink .edit-btn {
        border: none; background: none; color: var(--bleu); font-size: 12.5px; font-weight: 600;
        cursor: pointer; padding: 0;
    }
    .permalink input {
        width: auto; flex: 1 1 180px;
        display: none;
        padding: 6px 10px;
        font-size: 13px;
    }
    .permalink.is-editing code, .permalink.is-editing .edit-btn { display: none; }
    .permalink.is-editing input { display: block; }

    .publish-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .switch { position: relative; display: inline-block; width: 42px; height: 24px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .switch-track {
        position: absolute; inset: 0;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
        border-radius: 999px; cursor: pointer;
        transition: background-color .2s ease, border-color .2s ease;
    }
    .switch-track::before {
        content: ""; position: absolute; width: 18px; height: 18px; left: 2px; top: 2px;
        background: #fff; border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .3);
        transition: transform .2s ease;
    }
    .switch input:checked + .switch-track { background: var(--vert); border-color: var(--vert); }
    .switch input:checked + .switch-track::before { transform: translateX(18px); }

    .author-line {
        display: flex; align-items: center; gap: 8px;
        font-size: 12.5px; color: var(--plomb);
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--glass-bord);
    }
    .author-line svg { width: 14px; height: 14px; flex-shrink: 0; }

    .featured-image { display: flex; align-items: center; gap: 14px; }
    .featured-image__box {
        position: relative;
        width: 68px; height: 68px;
        border-radius: 12px;
        border: 1.5px dashed var(--glass-bord);
        background: var(--blanc-doux);
        display: flex; align-items: center; justify-content: center;
        color: var(--plomb);
        cursor: pointer;
        overflow: hidden;
        flex-shrink: 0;
        transition: border-color .15s ease;
    }
    .featured-image__box:hover { border-color: var(--vert); }
    .featured-image__box svg { width: 20px; height: 20px; }
    .featured-image__box img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .featured-image__box.has-image img { display: block; }
    .featured-image__box.has-image svg { display: none; }
    .featured-image__box input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

    .featured-image__meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .featured-image__meta strong { font-size: 13px; }
    .featured-image__meta span { font-size: 12px; color: var(--plomb); }
    .featured-image__remove {
        border: none; background: none; color: var(--magenta); font-size: 12px; font-weight: 600;
        cursor: pointer; padding: 0; text-align: left; display: none;
    }
    .featured-image.has-image .featured-image__remove { display: inline-block; }

    .image-extra { display: none; flex-direction: column; gap: 14px; margin-top: 16px; }
    .image-extra.is-visible { display: flex; }

    .side-stack { display: flex; flex-direction: column; gap: 24px; }

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
    .btn--outline { background: transparent; border-color: var(--glass-bord); color: inherit; }
    .btn--outline:hover { border-color: var(--bleu); color: var(--bleu); }
    .btn--ghost { background: transparent; color: var(--plomb); }
    .btn--ghost:hover { color: inherit; }

    /* ==================== En-tête de page + bouton d'ajout ==================== */
    .page-head-actions { display: flex; align-items: center; gap: 12px; }

    /* ==================== Liste des podcasts ==================== */
    .podcasts-table-wrap { overflow-x: auto; }
    .podcasts-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .podcasts-table th {
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
    .podcasts-table td {
        padding: 12px;
        border-bottom: 1px solid var(--glass-bord);
        vertical-align: middle;
    }
    .podcasts-table tbody tr { transition: background-color .15s ease; }
    .podcasts-table tbody tr:hover { background: var(--blanc-doux); }
    .podcasts-table tbody tr:last-child td { border-bottom: none; }

    .row-podcast { display: flex; align-items: center; gap: 12px; min-width: 220px; }
    .row-thumb {
        width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
        object-fit: cover; background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
    }
    .row-thumb--placeholder {
        display: flex; align-items: center; justify-content: center;
        color: var(--plomb);
    }
    .row-thumb--placeholder svg { width: 18px; height: 18px; }
    .row-podcast__title { font-weight: 600; line-height: 1.3; }
    .row-podcast__slug { font-size: 12px; color: var(--plomb); margin-top: 2px; }

    .row-soundcloud {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12.5px; color: var(--or); font-weight: 600;
        text-decoration: none; max-width: 200px;
    }
    .row-soundcloud svg { width: 14px; height: 14px; flex-shrink: 0; }
    .row-soundcloud span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .row-soundcloud:hover { color: var(--bleu); }

    .row-date { color: var(--plomb); white-space: nowrap; }

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

    /* ==================== Badge de statut (scopé — ne PAS réutiliser .live-badge global,
       qui est déjà utilisé/positionné pour le badge "EN LIGNE" de la sidebar) ==================== */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
        color: var(--plomb);
        white-space: nowrap;
    }
    .status-badge.is-live {
        color: var(--vert);
        border-color: rgba(25, 135, 84, .35);
    }
    .status-badge .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .empty-state {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        gap: 10px; padding: 48px 20px;
        color: var(--plomb);
    }
    .empty-state svg { width: 40px; height: 40px; opacity: .6; }
    .empty-state strong { color: inherit; font-size: 15px; }

    .pagination-wrap { margin-top: 18px; }
    .pagination-wrap--top { margin-top: 0; margin-bottom: 18px; }

    /* ==================== Modale (fond flouté) ==================== */
    .modal-overlay {
        position: fixed; inset: 0;
        display: flex; align-items: flex-start; justify-content: center;
        padding: 40px 16px;
        background: rgba(6, 8, 14, .55);
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
        width: 100%; max-width: 980px;
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
        border: 1px solid var(--glass-bord);
        border-radius: 20px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .45);
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
        .grid2 { grid-template-columns: 1fr; }
        .modal-overlay { padding: 0; align-items: stretch; }
        .modal-panel { max-width: none; height: 100%; }
        .modal-card { height: 100%; max-height: none; border-radius: 0; }
        .row-podcast { min-width: 160px; }
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
        box-shadow: 0 14px 34px rgba(0, 0, 0, .35);
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
    .toast--success { border-color: rgba(25, 135, 84, .45); }
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
        <h1>Podcasts <span>🎙️</span></h1>
        <p>Gérez les podcasts SoundCloud publiés sur SIOKA.</p>
    </div>
    <div class="page-head-actions">
        <button type="button" class="btn btn--primary" id="openCreateModalBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Nouveau podcast
        </button>
    </div>
</div>

{{-- ===================== Liste des podcasts ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="dash-sec-head">
            <div class="sec-head-icon">
                <div class="stat-icon" style="--stat-color:var(--or);">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Z" />
                        <path d="M19 11v1a7 7 0 0 1-14 0v-1M12 19v3" />
                    </svg>
                </div>
                <div class="dash-sec-title">Podcasts ({{ $podcasts->total() ?? (isset($podcasts) ? count($podcasts) : 0) }})</div>
            </div>
        </div>

        @if(isset($podcasts) && count($podcasts) > 0)
            @if(method_exists($podcasts, 'hasPages') && $podcasts->hasPages())
                <div class="pagination-wrap pagination-wrap--top">{{ $podcasts->links() }}</div>
            @endif

            <div class="podcasts-table-wrap">
                <table class="podcasts-table">
                    <thead>
                        <tr>
                            <th>Podcast</th>
                            <th>SoundCloud</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($podcasts as $podcast)
                            <tr>
                                <td>
                                    <div class="row-podcast">
                                        @if(!empty($podcast->image))
                                            <img class="row-thumb" src="{{ asset('storage/' . $podcast->image) }}" alt="">
                                        @else
                                            <div class="row-thumb row-thumb--placeholder">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Z"/><path d="M19 11v1a7 7 0 0 1-14 0v-1M12 19v3"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="row-podcast__title">{{ $podcast->title }}</div>
                                            <div class="row-podcast__slug">/{{ $podcast->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ $podcast->soundcloud_url }}" target="_blank" rel="noopener" class="row-soundcloud">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M1.6 15.5c-.1 0-.2.1-.2.2l-.3 2.1.3 2.1c0 .1.1.2.2.2s.2-.1.2-.2l.4-2.1-.4-2.1c0-.1-.1-.2-.2-.2Zm1.4-.6c-.1 0-.2.1-.2.2l-.3 2.7.3 2.6c0 .1.1.2.2.2s.2-.1.2-.2l.4-2.6-.4-2.7c0-.1-.1-.2-.2-.2Zm10.6-4.3c-1 0-1.9.3-2.6.8-.2-2.6-2.4-4.6-5-4.6-.6 0-1.2.1-1.7.3-.2.1-.3.2-.3.4v11.6c0 .2.2.4.4.4h9.2c1.8 0 3.3-1.5 3.3-3.3 0-1.9-1.4-3.4-3.3-3.6Z"/></svg>
                                        <span>SoundCloud</span>
                                    </a>
                                </td>
                                <td>{{ $podcast->user->name ?? '—' }}</td>
                                <td>
                                    <span class="status-badge {{ $podcast->is_published ? 'is-live' : '' }}">
                                        <span class="status-dot" style="background:{{ $podcast->is_published ? 'var(--vert)' : 'var(--plomb)' }}"></span>
                                        {{ $podcast->is_published ? 'En ligne' : 'Brouillon' }}
                                    </span>
                                </td>
                                <td class="row-date">{{ optional($podcast->created_at)->format('d/m/Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button"
                                                class="row-icon-btn js-edit-podcast"
                                                title="Modifier"
                                                data-podcast="{{ json_encode([
                                                    'id' => $podcast->id,
                                                    'title' => $podcast->title,
                                                    'slug' => $podcast->slug,
                                                    'description' => $podcast->description,
                                                    'soundcloud_url' => $podcast->soundcloud_url,
                                                    'is_published' => (bool) $podcast->is_published,
                                                    'image_url' => !empty($podcast->image) ? asset('storage/' . $podcast->image) : null,
                                                    'image_caption' => $podcast->image_caption,
                                                    'image_alt' => $podcast->image_alt,
                                                ]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/></svg>
                                        </button>
                                        <button type="button"
                                                class="row-icon-btn row-icon-btn--danger js-delete-podcast"
                                                title="Supprimer"
                                                data-action="{{ route('podcasts.destroy', $podcast->id) }}"
                                                data-title="{{ $podcast->title }}">
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Z"/><path d="M19 11v1a7 7 0 0 1-14 0v-1M12 19v3"/></svg>
                <strong>Aucun podcast pour le moment</strong>
                <p>Ajoutez votre premier podcast pour qu'il apparaisse ici.</p>
            </div>
        @endif
    </div>
</section>

{{-- ===================== Modale : formulaire de création / modification ===================== --}}
<div class="modal-overlay" id="podcastModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span id="modalIcon">🎙️</span>
                    <span id="modalTitleText">Nouveau podcast</span>
                </div>
                <button type="button" class="modal-card__close" id="closeModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="{{ route('podcasts.store') }}" method="POST" enctype="multipart/form-data" id="podcastForm" class="podcast-form" novalidate>
                    @csrf
                    <input type="hidden" name="_method" id="methodInput" value="{{ old('_method') }}">
                    <input type="hidden" name="id" id="podcastIdInput" value="{{ old('id') }}">
                    {{-- Auteur rempli automatiquement --}}
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    {{-- État piloté par le switch + les boutons d'action --}}
                    <input type="hidden" name="is_published" id="is_published_input" value="{{ old('is_published', 0) }}">

                    <div class="grid2">

                        {{-- ===================== Colonne principale : contenu ===================== --}}
                        <section class="panel">
                            <div class="panel-pad">

                                <div class="dash-sec-head">
                                    <div class="sec-head-icon">
                                        <div class="stat-icon" style="--stat-color:var(--or);">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Z" />
                                                <path d="M19 11v1a7 7 0 0 1-14 0v-1M12 19v3" />
                                            </svg>
                                        </div>
                                        <div class="dash-sec-title">Contenu du podcast</div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="title">Titre <span class="req">*</span></label>
                                    <input type="text" name="title" id="title" maxlength="180"
                                           value="{{ old('title') }}"
                                           class="{{ $errors->has('title') ? 'has-error' : '' }}"
                                           placeholder="Ex. : Épisode 12 — La musique malgache aujourd'hui"
                                           required>
                                    @error('title')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="field">
                                    <label>Adresse (slug)</label>
                                    <div class="permalink" id="permalink">
                                        <span>sioka.mg/podcasts/</span><code id="slugText">votre-slug</code>
                                        <button type="button" class="edit-btn" id="editSlugBtn">Modifier</button>
                                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="slug-du-podcast" class="{{ $errors->has('slug') ? 'has-error' : '' }}">
                                    </div>
                                    @error('slug')
                                        <p class="field-error">{{ $message }}</p>
                                    @else
                                        <p class="field-hint">Généré automatiquement depuis le titre, modifiable si besoin.</p>
                                    @enderror
                                </div>

                                <div class="field">
                                    <div class="field-row">
                                        <label for="description">Description <span class="req">*</span></label>
                                        <span class="char-counter" id="descriptionCounter">0 / 200</span>
                                    </div>
                                    <textarea name="description" id="description" rows="3" maxlength="200"
                                              class="{{ $errors->has('description') ? 'has-error' : '' }}"
                                              placeholder="Un court résumé de cet épisode"
                                              required>{{ old('description') }}</textarea>
                                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="field">
                                    <label for="soundcloud_url">Lien SoundCloud <span class="req">*</span></label>
                                    <input type="url" name="soundcloud_url" id="soundcloud_url" maxlength="500"
                                           value="{{ old('soundcloud_url') }}"
                                           class="{{ $errors->has('soundcloud_url') ? 'has-error' : '' }}"
                                           placeholder="https://soundcloud.com/sioka/episode-12"
                                           required>
                                    @error('soundcloud_url')
                                        <p class="field-error">{{ $message }}</p>
                                    @else
                                        <p class="field-hint">Lien de l'épisode sur SoundCloud, utilisé pour le lecteur intégré.</p>
                                    @enderror
                                </div>

                            </div>
                        </section>

                        {{-- ===================== Colonne latérale : réglages ===================== --}}
                        <div class="side-stack">

                            <section class="panel">
                                <div class="panel-pad">
                                    <div class="dash-sec-head">
                                        <div class="sec-head-icon">
                                            <div class="stat-icon" style="--stat-color:var(--magenta);">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="M8 12h8M12 8v8" />
                                                </svg>
                                            </div>
                                            <div class="dash-sec-title">Publication</div>
                                        </div>
                                    </div>

                                    <div class="publish-row">
                                        <span class="status-badge" id="statusBadge">
                                            <span class="status-dot" id="statusDot"></span><span id="publishStatusLabel">Brouillon</span>
                                        </span>
                                        <label class="switch">
                                            <input type="checkbox" id="is_published_toggle" @checked(old('is_published'))>
                                            <span class="switch-track" aria-hidden="true"></span>
                                        </label>
                                    </div>
                                    @error('is_published')<p class="field-error">{{ $message }}</p>@enderror

                                    <div class="author-line">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" d="M5 20c1.5-4 5-5.5 7-5.5S17.5 16 19 20"/></svg>
                                        {{ auth()->user()->name ?? 'Utilisateur connecté' }}
                                    </div>
                                </div>
                            </section>

                            <section class="panel">
                                <div class="panel-pad">
                                    <div class="dash-sec-head">
                                        <div class="sec-head-icon">
                                            <div class="stat-icon" style="--stat-color:var(--vert);">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M4 5h16v14H4V5Z" />
                                                    <path d="m4 16 4.5-4.5a2 2 0 0 1 2.8 0L15 15m0 0 1.5-1.5a2 2 0 0 1 2.8 0L20 15" />
                                                    <circle cx="8.5" cy="9" r="1.5" />
                                                </svg>
                                            </div>
                                            <div class="dash-sec-title">Image de couverture</div>
                                        </div>
                                    </div>

                                    <div class="featured-image" id="featuredImage">
                                        <label class="featured-image__box" id="imageBox">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4V5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m4 16 4.5-4.5a2 2 0 0 1 2.8 0L15 15m0 0 1.5-1.5a2 2 0 0 1 2.8 0L20 15"/><circle cx="8.5" cy="9" r="1.5"/></svg>
                                            <img src="" alt="" id="imagePreview">
                                            <input type="file" name="image" id="image" accept="image/png, image/jpeg, image/webp">
                                        </label>
                                        <div class="featured-image__meta">
                                            <strong>Ajouter une image</strong>
                                            <span>JPG, PNG, WebP — 10 Mo max</span>
                                            <button type="button" class="featured-image__remove" id="imageRemoveBtn">Retirer</button>
                                        </div>
                                    </div>
                                    @error('image')<p class="field-error">{{ $message }}</p>@enderror

                                    <div class="image-extra" id="imageExtra">
                                        <div class="field">
                                            <label for="image_caption">Légende</label>
                                            <input type="text" name="image_caption" id="image_caption" value="{{ old('image_caption') }}" placeholder="Ex. : Cover de l'épisode 12">
                                            @error('image_caption')<p class="field-error">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="field">
                                            <label for="image_alt">Texte alternatif</label>
                                            <input type="text" name="image_alt" id="image_alt" value="{{ old('image_alt') }}" placeholder="Décrit l'image pour l'accessibilité">
                                            @error('image_alt')<p class="field-error">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>

                        </div>
                    </div>

                    {{-- ===================== Actions ===================== --}}
                    <div class="editor-actions">
                        <button type="button" class="btn btn--ghost" id="cancelModalBtn">Annuler</button>
                        <button type="submit" class="btn btn--outline" onclick="return AE.submitAs(0)">Enregistrer comme brouillon</button>
                        <button type="submit" class="btn btn--primary" id="submitPublishBtn" onclick="return AE.submitAs(1)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l6 6L20 6"/></svg>
                            <span id="submitPublishText">Publier</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : confirmation de suppression ===================== --}}
<div class="modal-overlay" id="podcastDeleteModalOverlay">
    <div class="modal-panel" style="max-width: 420px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer le podcast</span>
                </div>
                <button type="button" class="modal-card__close" id="closeDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p style="font-size:14px; line-height:1.6; color: var(--plomb); margin: 0 0 20px;">
                    Supprimer définitivement le podcast « <strong id="deletePodcastTitle" style="color: inherit;"></strong> » ? Cette action est irréversible.
                </p>
                <form action="" method="POST" id="podcastDeleteForm" style="display:flex; gap:10px;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn--ghost" id="cancelDeleteBtn" style="flex:1; justify-content:center; border: 1px solid var(--glass-bord);">
                        Annuler
                    </button>
                    <button type="submit" class="btn" style="flex:1; justify-content:center; background: var(--magenta); color:#fff;">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Script : liste, modale (ouverture/fermeture) + formulaire de création/modification --}}
<script>
    (function () {
        const form = document.getElementById('podcastForm');
        if (!form) return;

        const storeUrl = @json(route('podcasts.store'));
        const updateUrlTemplate = @json(route('podcasts.update', ['podcast' => '__ID__']));

        // ---------- Modale : ouverture / fermeture ----------
        const overlay = document.getElementById('podcastModalOverlay');
        const modalTitleText = document.getElementById('modalTitleText');
        const modalIcon = document.getElementById('modalIcon');
        const submitPublishText = document.getElementById('submitPublishText');
        const methodInput = document.getElementById('methodInput');
        const podcastIdInput = document.getElementById('podcastIdInput');

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
            form.action = storeUrl;
            methodInput.value = '';
            podcastIdInput.value = '';
            modalTitleText.textContent = 'Nouveau podcast';
            modalIcon.textContent = '🎙️';
            submitPublishText.textContent = 'Publier';
            slugManuallyEdited = false;
            refreshSlugText();
            permalink.classList.remove('is-editing');
            updateDescriptionCounter();
            clearImagePreview();
            syncPublishState(false);
        }

        function fillFormForEdit(data) {
            form.action = updateUrlTemplate.replace('__ID__', data.id);
            methodInput.value = 'PUT';
            podcastIdInput.value = data.id;

            titleInput.value = data.title || '';
            slugInput.value = data.slug || '';
            slugManuallyEdited = true;
            refreshSlugText();
            permalink.classList.remove('is-editing');

            description.value = data.description || '';
            updateDescriptionCounter();

            document.getElementById('soundcloud_url').value = data.soundcloud_url || '';
            document.getElementById('image_caption').value = data.image_caption || '';
            document.getElementById('image_alt').value = data.image_alt || '';

            if (data.image_url) {
                imagePreview.src = data.image_url;
                imageBox.classList.add('has-image');
                featuredImage.classList.add('has-image');
                imageExtra.classList.add('is-visible');
            } else {
                clearImagePreview();
            }

            syncPublishState(!!data.is_published);

            modalTitleText.textContent = 'Modifier le podcast';
            modalIcon.textContent = '✏️';
            submitPublishText.textContent = 'Publier les modifications';
        }

        // Bouton(s) d'ouverture en mode création
        ['openCreateModalBtn', 'openCreateModalBtnEmpty'].forEach(function (id) {
            const btn = document.getElementById(id);
            if (btn) btn.addEventListener('click', function () {
                resetFormToCreateMode();
                openModal();
                titleInput.focus();
            });
        });

        // Boutons "Modifier" de chaque ligne
        document.querySelectorAll('.js-edit-podcast').forEach(function (btn) {
            btn.addEventListener('click', function () {
                try {
                    const data = JSON.parse(btn.getAttribute('data-podcast'));
                    fillFormForEdit(data);
                    openModal();
                } catch (e) {
                    console.error('Impossible de lire les données du podcast', e);
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
        const deleteOverlay = document.getElementById('podcastDeleteModalOverlay');
        const deleteForm = document.getElementById('podcastDeleteForm');
        const deletePodcastTitle = document.getElementById('deletePodcastTitle');
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

        document.querySelectorAll('.js-delete-podcast').forEach(function (btn) {
            btn.addEventListener('click', function () {
                deleteForm.action = btn.dataset.action;
                deletePodcastTitle.textContent = btn.dataset.title;
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

        // ---------- Slug automatique + édition inline façon WordPress ----------
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        const slugText = document.getElementById('slugText');
        const permalink = document.getElementById('permalink');
        const editSlugBtn = document.getElementById('editSlugBtn');
        let slugManuallyEdited = slugInput.value.trim().length > 0;

        function slugify(text) {
            return text.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .toLowerCase().trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }

        function refreshSlugText() {
            slugText.textContent = slugInput.value.trim() || 'votre-slug';
        }

        titleInput.addEventListener('input', () => {
            if (!slugManuallyEdited) {
                slugInput.value = slugify(titleInput.value);
                refreshSlugText();
            }
        });

        editSlugBtn.addEventListener('click', () => {
            permalink.classList.add('is-editing');
            slugInput.focus();
            slugInput.select();
        });
        slugInput.addEventListener('blur', () => {
            slugManuallyEdited = slugInput.value.trim().length > 0;
            refreshSlugText();
            permalink.classList.remove('is-editing');
        });
        slugInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); slugInput.blur(); }
        });

        refreshSlugText();
        if (slugInput.value.trim()) permalink.classList.remove('is-editing');

        // ---------- Compteur de caractères de la description ----------
        const description = document.getElementById('description');
        const descriptionCounter = document.getElementById('descriptionCounter');
        function updateDescriptionCounter() {
            const len = description.value.length;
            descriptionCounter.textContent = len + ' / 200';
            descriptionCounter.classList.toggle('is-over', len >= 200);
        }
        description.addEventListener('input', updateDescriptionCounter);
        updateDescriptionCounter();

        // ---------- Image de couverture (vignette + aperçu + divulgation progressive) ----------
        const imageInput = document.getElementById('image');
        const imageBox = document.getElementById('imageBox');
        const imagePreview = document.getElementById('imagePreview');
        const featuredImage = document.getElementById('featuredImage');
        const imageExtra = document.getElementById('imageExtra');
        const imageRemoveBtn = document.getElementById('imageRemoveBtn');

        function showImage(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imageBox.classList.add('has-image');
                featuredImage.classList.add('has-image');
                imageExtra.classList.add('is-visible');
            };
            reader.readAsDataURL(file);
        }

        function clearImagePreview() {
            imageInput.value = '';
            imagePreview.src = '';
            imageBox.classList.remove('has-image');
            featuredImage.classList.remove('has-image');
            imageExtra.classList.remove('is-visible');
        }

        imageInput.addEventListener('change', () => showImage(imageInput.files && imageInput.files[0]));
        imageRemoveBtn.addEventListener('click', clearImagePreview);

        // ---------- Switch de publication ----------
        const toggle = document.getElementById('is_published_toggle');
        const hiddenInput = document.getElementById('is_published_input');
        const statusLabel = document.getElementById('publishStatusLabel');
        const statusDot = document.getElementById('statusDot');
        const statusBadge = document.getElementById('statusBadge');

        function syncPublishState(isPublished) {
            hiddenInput.value = isPublished ? 1 : 0;
            toggle.checked = isPublished;
            statusLabel.textContent = isPublished ? 'En ligne' : 'Brouillon';
            statusBadge.classList.toggle('is-live', isPublished);
            statusDot.style.background = isPublished ? 'var(--vert)' : 'var(--plomb)';
        }
        toggle.addEventListener('change', () => syncPublishState(toggle.checked));
        syncPublishState(toggle.checked);

        // ---------- Boutons d'action (forcent l'état avant l'envoi) ----------
        window.AE = window.AE || {};
        window.AE.submitAs = function (isPublished) {
            syncPublishState(!!isPublished);
            return true;
        };

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
                    modalTitleText.textContent = 'Modifier le podcast';
                    modalIcon.textContent = '✏️';
                    submitPublishText.textContent = 'Publier les modifications';
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