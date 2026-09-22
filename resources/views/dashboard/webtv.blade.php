@extends('layouts.dashboard-layout')

@section('title', "WebTV - SIOKA")

@section('content')


<style>
    /* ==================== Formulaire (même esprit que le formulaire articles) ==================== */
    .article-form .field { margin-bottom: 18px; }
    .article-form .field:last-child { margin-bottom: 0; }

    .article-form label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }
    .article-form .req { color: var(--magenta); font-weight: 700; }

    .article-form input[type="text"],
    .article-form select,
    .article-form textarea {
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
    .article-form textarea { resize: vertical; line-height: 1.6; }
    .article-form select option {
        background: var(--ink-2);
        color: var(--blanc);
    }
    .article-form input:focus,
    .article-form select:focus,
    .article-form textarea:focus {
        outline: none;
        border-color: var(--bleu);
        box-shadow: 0 0 0 3px var(--bleu-glow);
    }
    .article-form .has-error { border-color: var(--magenta) !important; }

    .field-row { display: flex; align-items: baseline; justify-content: space-between; }
    .field-hint { margin: 6px 0 0; font-size: 12px; color: var(--plomb); }
    .field-error { margin: 6px 0 0; font-size: 12px; color: var(--magenta); font-weight: 600; }
    .char-counter { font-size: 11.5px; color: var(--plomb); }
    .char-counter.is-over { color: var(--magenta); font-weight: 700; }

    .sec-head-icon { display: flex; align-items: center; gap: 10px; }
    .sec-head-icon .stat-icon { width: 34px; height: 34px; }
    .sec-head-icon .stat-icon svg { width: 16px; height: 16px; }

    .author-line {
        display: flex; align-items: center; gap: 8px;
        font-size: 12.5px; color: var(--plomb);
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--glass-bord);
    }
    .author-line svg { width: 14px; height: 14px; flex-shrink: 0; }

    /* ==================== Zone de sélection du fichier vidéo (même logique que featured-image) ==================== */
    .video-drop { display: flex; align-items: center; gap: 14px; }
    .video-drop__box {
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
    .video-drop__box:hover { border-color: var(--vert); }
    .video-drop__box svg { width: 20px; height: 20px; }
    .video-drop__box.has-file { border-style: solid; border-color: var(--vert); color: var(--vert); }
    .video-drop__box input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

    .video-drop__meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .video-drop__meta strong { font-size: 13px; }
    .video-drop__meta span { font-size: 12px; color: var(--plomb); word-break: break-all; }
    .video-drop__remove {
        border: none; background: none; color: var(--magenta); font-size: 12px; font-weight: 600;
        cursor: pointer; padding: 0; text-align: left; display: none;
    }
    .video-drop.has-file .video-drop__remove { display: inline-block; }

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
    .btn--primary:disabled { opacity: .6; cursor: not-allowed; box-shadow: none; }
    .btn--outline { background: transparent; border-color: var(--glass-bord); color: inherit; }
    .btn--outline:hover { border-color: var(--bleu); color: var(--bleu); }
    .btn--ghost { background: transparent; color: var(--plomb); }
    .btn--ghost:hover { color: inherit; }

    /* ==================== En-tête de page + bouton d'ajout ==================== */
    .page-head-actions { display: flex; align-items: center; gap: 12px; }

    /* ==================== Liste des vidéos ==================== */
    .articles-table-wrap { overflow-x: auto; }
    .articles-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .articles-table th {
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
    .articles-table td {
        padding: 12px;
        border-bottom: 1px solid var(--glass-bord);
        vertical-align: middle;
    }
    .articles-table tbody tr { transition: background-color .15s ease; }
    .articles-table tbody tr:hover { background: var(--blanc-doux); }
    .articles-table tbody tr:last-child td { border-bottom: none; }

    .row-article { display: flex; align-items: center; gap: 12px; min-width: 220px; }
    .row-thumb {
        width: 64px; height: 44px; border-radius: 10px; flex-shrink: 0;
        object-fit: cover; background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
    }
    .row-thumb--placeholder {
        display: flex; align-items: center; justify-content: center;
        color: var(--plomb);
    }
    .row-thumb--placeholder svg { width: 18px; height: 18px; }
    .row-article__title { font-weight: 600; line-height: 1.3; }
    .row-article__slug { font-size: 12px; color: var(--plomb); margin-top: 2px; }

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
        text-decoration: none;
        transition: border-color .15s ease, color .15s ease, background-color .15s ease;
    }
    .row-icon-btn svg { width: 15px; height: 15px; }
    .row-icon-btn:hover { color: var(--bleu); border-color: var(--bleu); }
    .row-icon-btn--danger:hover { color: var(--magenta); border-color: var(--magenta); }

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

    /* ==================== Pagination (identique à Actualités) ==================== */
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
        width: 100%; max-width: 820px;
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
        .row-article { min-width: 160px; }
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
    .toast--error { border-color: rgba(220, 53, 69, .5); }
    .toast--error .toast__icon { color: var(--magenta); }
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

{{-- ===================== Toast de notification (succès / erreur) ===================== --}}
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
@if(session('error'))
    <div class="toast toast--error" id="pageToastError">
        <span class="toast__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v5M12 16h.01"/></svg>
        </span>
        <span>{{ session('error') }}</span>
        <button type="button" class="toast__close" id="pageToastErrorClose" aria-label="Fermer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
@endif

{{-- ===================== En-tête, dans le style de la vue d'ensemble ===================== --}}
<div class="welcome">
    <div>
        <h1>WebTV <span>📺</span></h1>
        <p>Gérez les vidéos publiées sur la chaîne YouTube de SIOKA.</p>
    </div>
    <div class="page-head-actions">
        <button type="button" class="btn btn--primary" id="openCreateModalBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Nouvelle vidéo
        </button>
    </div>
</div>

{{-- ===================== Liste des vidéos ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="dash-sec-head">
            <div class="sec-head-icon">
                <div class="stat-icon" style="--stat-color:var(--bleu);">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m10 8 6 4-6 4V8Z" />
                        <rect x="2" y="4" width="20" height="16" rx="2" />
                    </svg>
                </div>
                <div class="dash-sec-title">Vidéos ({{ $videos->total() }})</div>
            </div>
        </div>

        @if($videos->count() > 0)
            @if(method_exists($videos, 'hasPages') && $videos->hasPages())
                <div class="pagination-wrap pagination-wrap--top">{{ $videos->links() }}</div>
            @endif

            <div class="articles-table-wrap">
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th>Vidéo</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($videos as $video)
                            @php
                                $videoId = $video['contentDetails']['videoId'] ?? null;
                                $thumb = $video['snippet']['thumbnails']['medium']['url'] ?? ($video['snippet']['thumbnails']['default']['url'] ?? null);
                                $publishedAt = $video['snippet']['publishedAt'] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="row-article">
                                        @if($thumb)
                                            <img class="row-thumb" src="{{ $thumb }}" alt="">
                                        @else
                                            <div class="row-thumb row-thumb--placeholder">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m10 8 6 4-6 4V8Z"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="row-article__title">{{ $video['snippet']['title'] ?? '—' }}</div>
                                            <div class="row-article__slug">{{ $videoId }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge is-live">
                                        <span class="status-dot" style="background:var(--vert)"></span>
                                        Sur YouTube
                                    </span>
                                </td>
                                <td class="row-date">{{ $publishedAt ? \Illuminate\Support\Carbon::parse($publishedAt)->format('d/m/Y') : '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        @if($videoId)
                                            <a href="https://www.youtube.com/watch?v={{ $videoId }}" target="_blank" rel="noopener" class="row-icon-btn" title="Voir sur YouTube">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6v6M10 14 21 3"/></svg>
                                            </a>
                                            <button type="button"
                                                    class="row-icon-btn js-edit-video"
                                                    title="Modifier"
                                                    data-video="{{ json_encode([
                                                        'id' => $videoId,
                                                        'title' => $video['snippet']['title'] ?? '',
                                                        'description' => $video['snippet']['description'] ?? '',
                                                    ]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/></svg>
                                            </button>
                                            <button type="button"
                                                    class="row-icon-btn row-icon-btn--danger js-delete-video"
                                                    title="Supprimer"
                                                    data-action="{{ route('webtv.destroy', $videoId) }}"
                                                    data-title="{{ $video['snippet']['title'] ?? '' }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 7"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m10 8 6 4-6 4V8Z"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                <strong>Aucune vidéo pour le moment</strong>
                <p>Publiez votre première vidéo pour qu'elle apparaisse ici.</p>
            </div>
        @endif
    </div>
</section>

{{-- ===================== Modale : formulaire de publication ===================== --}}
<div class="modal-overlay" id="videoModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>📺</span>
                    <span>Nouvelle vidéo</span>
                </div>
                <button type="button" class="modal-card__close" id="closeModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="{{ route('webtv.store') }}" method="POST" enctype="multipart/form-data" id="videoForm" class="article-form" novalidate>
                    @csrf

                    <div class="grid2">

                        {{-- ===================== Colonne principale ===================== --}}
                        <section class="panel">
                            <div class="panel-pad">

                                <div class="dash-sec-head">
                                    <div class="sec-head-icon">
                                        <div class="stat-icon" style="--stat-color:var(--bleu);">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="m10 8 6 4-6 4V8Z" />
                                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                            </svg>
                                        </div>
                                        <div class="dash-sec-title">Détails de la vidéo</div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="title">Titre <span class="req">*</span></label>
                                    <input type="text" name="title" id="title" maxlength="100"
                                           value="{{ old('title') }}"
                                           class="{{ $errors->has('title') ? 'has-error' : '' }}"
                                           placeholder="Ex. : Journal du soir — 9 août 2026"
                                           required>
                                    @error('title')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="field">
                                    <div class="field-row">
                                        <label for="description">Description</label>
                                        <span class="char-counter" id="descCounter">0 / 5000</span>
                                    </div>
                                    <textarea name="description" id="description" rows="6" maxlength="5000"
                                              class="{{ $errors->has('description') ? 'has-error' : '' }}"
                                              placeholder="Décrivez le contenu de la vidéo">{{ old('description') }}</textarea>
                                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                                </div>

                            </div>
                        </section>

                        {{-- ===================== Colonne latérale ===================== --}}
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
                                            <div class="dash-sec-title">Visibilité</div>
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="privacyStatus" class="sr-only">Visibilité</label>
                                        <select name="privacyStatus" id="privacyStatus">
                                            <option value="public" @selected(old('privacyStatus', 'public') == 'public')>Publique</option>
                                            <option value="unlisted" @selected(old('privacyStatus') == 'unlisted')>Non répertoriée</option>
                                            <option value="private" @selected(old('privacyStatus') == 'private')>Privée</option>
                                        </select>
                                        @error('privacyStatus')<p class="field-error">{{ $message }}</p>@enderror
                                    </div>

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
                                                    <path d="m10 8 6 4-6 4V8Z" />
                                                    <rect x="2" y="4" width="20" height="16" rx="2" />
                                                </svg>
                                            </div>
                                            <div class="dash-sec-title">Fichier vidéo</div>
                                        </div>
                                    </div>

                                    <div class="video-drop" id="videoDrop">
                                        <label class="video-drop__box" id="videoBox">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m10 8 6 4-6 4V8Z"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                                            <input type="file" name="video" id="video" accept="video/mp4,video/quicktime" required>
                                        </label>
                                        <div class="video-drop__meta">
                                            <strong id="videoFileName">Choisir un fichier</strong>
                                            <span>MP4, MOV — 500 Mo max</span>
                                            <button type="button" class="video-drop__remove" id="videoRemoveBtn">Retirer</button>
                                        </div>
                                    </div>
                                    @error('video')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            </section>

                        </div>
                    </div>

                    {{-- ===================== Actions ===================== --}}
                    <div class="editor-actions">
                        <button type="button" class="btn btn--ghost" id="cancelModalBtn">Annuler</button>
                        <button type="submit" class="btn btn--primary" id="submitUploadBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l6 6L20 6"/></svg>
                            <span id="submitUploadText">Publier sur YouTube</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : modification du titre / description ===================== --}}
<div class="modal-overlay" id="videoEditModalOverlay">
    <div class="modal-panel" style="max-width: 620px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>✏️</span>
                    <span>Modifier la vidéo</span>
                </div>
                <button type="button" class="modal-card__close" id="closeEditModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="" method="POST" id="videoEditForm" class="article-form" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="field">
                        <label for="edit_title">Titre <span class="req">*</span></label>
                        <input type="text" name="title" id="edit_title" maxlength="100" required
                               placeholder="Titre de la vidéo">
                    </div>

                    <div class="field">
                        <div class="field-row">
                            <label for="edit_description">Description</label>
                            <span class="char-counter" id="editDescCounter">0 / 5000</span>
                        </div>
                        <textarea name="description" id="edit_description" rows="6" maxlength="5000"
                                  placeholder="Décrivez le contenu de la vidéo"></textarea>
                    </div>

                    <div class="editor-actions">
                        <button type="button" class="btn btn--ghost" id="cancelEditModalBtn">Annuler</button>
                        <button type="submit" class="btn btn--primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l6 6L20 6"/></svg>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : confirmation de suppression ===================== --}}
<div class="modal-overlay" id="videoDeleteModalOverlay">
    <div class="modal-panel" style="max-width: 420px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer la vidéo</span>
                </div>
                <button type="button" class="modal-card__close" id="closeDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p style="font-size:14px; line-height:1.6; color: var(--plomb); margin: 0 0 20px;">
                    Supprimer définitivement la vidéo « <strong id="deleteVideoTitle" style="color: inherit;"></strong> » de YouTube ? Cette action est irréversible.
                </p>
                <form action="" method="POST" id="videoDeleteForm" style="display:flex; gap:10px;">
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
<script>
    (function () {
        const form = document.getElementById('videoForm');
        if (!form) return;

        // ---------- Modale : ouverture / fermeture ----------
        const overlay = document.getElementById('videoModalOverlay');

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

        document.getElementById('openCreateModalBtn').addEventListener('click', function () {
            openModal();
            document.getElementById('title').focus();
        });
        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('cancelModalBtn').addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
        });

        // ---------- Modale : modification du titre / description ----------
        const editOverlay = document.getElementById('videoEditModalOverlay');
        const editForm = document.getElementById('videoEditForm');
        const editTitleInput = document.getElementById('edit_title');
        const editDescInput = document.getElementById('edit_description');
        const editDescCounter = document.getElementById('editDescCounter');
        const updateUrlTemplate = @json(route('webtv.update', ['videoId' => '__ID__']));

        function updateEditDescCounter() {
            const len = editDescInput.value.length;
            editDescCounter.textContent = len + ' / 5000';
            editDescCounter.classList.toggle('is-over', len >= 5000);
        }
        editDescInput.addEventListener('input', updateEditDescCounter);

        function openEditModal() {
            editOverlay.classList.add('is-open');
            lockScroll(true);
        }
        function closeEditModal() {
            editOverlay.classList.remove('is-open');
            lockScroll(false);
        }

        document.querySelectorAll('.js-edit-video').forEach(function (btn) {
            btn.addEventListener('click', function () {
                try {
                    const data = JSON.parse(btn.getAttribute('data-video'));
                    editForm.action = updateUrlTemplate.replace('__ID__', data.id);
                    editTitleInput.value = data.title || '';
                    editDescInput.value = data.description || '';
                    updateEditDescCounter();
                    openEditModal();
                    editTitleInput.focus();
                } catch (e) {
                    console.error('Impossible de lire les données de la vidéo', e);
                }
            });
        });

        document.getElementById('closeEditModalBtn').addEventListener('click', closeEditModal);
        document.getElementById('cancelEditModalBtn').addEventListener('click', closeEditModal);
        editOverlay.addEventListener('click', function (e) {
            if (e.target === editOverlay) closeEditModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && editOverlay.classList.contains('is-open')) closeEditModal();
        });

        // ---------- Modale : confirmation de suppression ----------
        const deleteOverlay = document.getElementById('videoDeleteModalOverlay');
        const deleteForm = document.getElementById('videoDeleteForm');
        const deleteVideoTitle = document.getElementById('deleteVideoTitle');
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

        document.querySelectorAll('.js-delete-video').forEach(function (btn) {
            btn.addEventListener('click', function () {
                deleteForm.action = btn.dataset.action;
                deleteVideoTitle.textContent = btn.dataset.title;
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

        // ---------- Compteur de caractères de la description ----------
        const description = document.getElementById('description');
        const descCounter = document.getElementById('descCounter');
        function updateDescCounter() {
            const len = description.value.length;
            descCounter.textContent = len + ' / 5000';
            descCounter.classList.toggle('is-over', len >= 5000);
        }
        description.addEventListener('input', updateDescCounter);
        updateDescCounter();

        // ---------- Sélection du fichier vidéo ----------
        const videoInput = document.getElementById('video');
        const videoBox = document.getElementById('videoBox');
        const videoDrop = document.getElementById('videoDrop');
        const videoFileName = document.getElementById('videoFileName');
        const videoRemoveBtn = document.getElementById('videoRemoveBtn');

        function showFile(file) {
            if (!file) return;
            videoFileName.textContent = file.name;
            videoBox.classList.add('has-file');
            videoDrop.classList.add('has-file');
        }

        function clearFile() {
            videoInput.value = '';
            videoFileName.textContent = 'Choisir un fichier';
            videoBox.classList.remove('has-file');
            videoDrop.classList.remove('has-file');
        }

        videoInput.addEventListener('change', () => showFile(videoInput.files && videoInput.files[0]));
        videoRemoveBtn.addEventListener('click', clearFile);

        // ---------- Envoi : désactive le bouton pendant l'upload (peut être long) ----------
        const submitBtn = document.getElementById('submitUploadBtn');
        const submitText = document.getElementById('submitUploadText');
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitText.textContent = 'Publication en cours…';
        });

        // ---------- Toasts de notification (auto-disparition) ----------
        [
            ['pageToast', 'pageToastClose'],
            ['pageToastError', 'pageToastErrorClose'],
        ].forEach(function (ids) {
            const toast = document.getElementById(ids[0]);
            if (!toast) return;
            const closeBtn = document.getElementById(ids[1]);
            requestAnimationFrame(function () {
                toast.classList.add('is-visible');
            });
            let toastTimer = setTimeout(hideToast, 5000);
            function hideToast() {
                toast.classList.remove('is-visible');
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    clearTimeout(toastTimer);
                    hideToast();
                });
            }
        });

        // ---------- Réouverture automatique de la modale après une erreur de validation ----------
        @if($errors->any())
            openModal();
        @endif
    })();
</script>
@endpush