@extends('layouts.dashboard-layout')

@section('title', "Boîte aux lettres - SIOKA")

@section('content')

@php
    // Pastille de couleur par objet (tokens existants uniquement)
    $couleursObjet = [
        'Suggestion'  => 'var(--magenta)',
        'Signalement' => 'var(--or)',
        'Question'    => 'var(--bleu)',
        'Témoignage'  => 'var(--vert)',
        'Autre'       => 'var(--plomb)',
    ];
@endphp

<style>
    /* ==================== En-tête de section ==================== */
    .sec-head-icon { display: flex; align-items: center; gap: 10px; }
    .sec-head-icon .stat-icon { width: 34px; height: 34px; }
    .sec-head-icon .stat-icon svg { width: 16px; height: 16px; }

    /* ==================== Boutons ==================== */
    .editor-actions {
        display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;
        margin-top: 28px;
    }
    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 18px; font-size: 13.5px; font-weight: 600;
        border-radius: 999px; border: 1px solid transparent;
        cursor: pointer; text-decoration: none; line-height: 1.2;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, color .15s ease;
        background: none;
    }
    .btn:active { transform: translateY(1px); }
    .btn svg { width: 15px; height: 15px; }
    .btn--ghost { background: transparent; color: var(--plomb); border-color: var(--glass-bord); }
    .btn--ghost:hover { color: inherit; }
    .btn--danger { background: var(--magenta); color: var(--fixe-clair); }

    /* ==================== Filtre par état ==================== */
    .msg-tabs { display: flex; gap: 8px; overflow-x: auto; padding: 2px 0 8px; margin: 0 0 14px; scrollbar-width: thin; }
    .msg-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 999px;
        border: 1px solid var(--glass-bord); background: var(--blanc-doux);
        color: inherit; font-size: 13px; font-weight: 600;
        white-space: nowrap; text-decoration: none;
        transition: border-color .15s ease, transform .15s ease;
    }
    .msg-tab:hover { border-color: var(--bleu); transform: translateY(-1px); }
    .msg-tab.is-active { background: linear-gradient(90deg, var(--magenta), var(--bleu)); border-color: transparent; color: var(--fixe-clair); }
    .msg-tab__count {
        min-width: 20px; padding: 1px 7px; border-radius: 999px; text-align: center;
        font-size: 11px; font-weight: 700;
        border: 1px solid currentColor; opacity: .8;
    }

    /* ==================== Tableau des messages ==================== */
    .msg-table-wrap { overflow-x: auto; }
    .msg-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .msg-table th {
        text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em;
        color: var(--plomb); font-weight: 700; padding: 0 12px 10px;
        border-bottom: 1px solid var(--glass-bord); white-space: nowrap;
    }
    .msg-table td { padding: 12px; border-bottom: 1px solid var(--glass-bord); vertical-align: middle; }
    .msg-row {
        cursor: pointer;
        transition: background-color .2s ease;
    }
    .msg-row:hover { background: var(--blanc-doux); }

    /* ---- Statut lu / non lu : pastille (non lu) ou coche (lu) ---- */
    .msg-status-cell { width: 44px; padding-right: 0 !important; }
    .msg-status { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; }
    .msg-status svg { width: 18px; height: 18px; }
    .msg-status__new  { color: var(--magenta); }
    .msg-status__read { color: var(--vert); }
    .msg-row.is-unread .msg-status__read { display: none; }
    .msg-row:not(.is-unread) .msg-status__new { display: none; }

    /* Message non lu : liseré magenta, fond légèrement teinté, texte en gras */
    .msg-row.is-unread { background: color-mix(in srgb, var(--magenta) 6%, transparent); }
    .msg-row.is-unread:hover { background: color-mix(in srgb, var(--magenta) 10%, transparent); }
    .msg-row.is-unread td:first-child { box-shadow: inset 3px 0 0 var(--magenta); }
    .msg-row.is-unread .msg-from,
    .msg-row.is-unread .msg-excerpt { font-weight: 600; color: var(--blanc); }
    .msg-row.is-unread .msg-date { color: var(--blanc); }

    /* Message lu : texte atténué, graisse normale */
    .msg-row:not(.is-unread) .msg-from { font-weight: 400; color: var(--plomb); }
    .msg-row:not(.is-unread) .msg-excerpt { font-weight: 400; }

    .msg-subject {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 3px 10px; border-radius: 999px; white-space: nowrap;
        font-size: 12px; font-weight: 600;
        background: var(--blanc-doux); border: 1px solid var(--glass-bord);
    }
    .msg-subject::before {
        content: ''; width: 7px; height: 7px; border-radius: 50%;
        background: var(--objet-color, var(--plomb));
    }

    .msg-from { min-width: 120px; line-height: 1.3; }
    .msg-contact { margin-top: 2px; font-size: 12px; color: var(--plomb); }
    .msg-excerpt { color: var(--plomb); max-width: 380px; }
    .msg-date { white-space: nowrap; color: var(--plomb); font-size: 12.5px; }

    .row-actions { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
    .row-icon-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 9px;
        border: 1px solid var(--glass-bord); background: var(--blanc-doux);
        color: var(--plomb); cursor: pointer;
        transition: border-color .15s ease, color .15s ease, background-color .15s ease;
    }
    .row-icon-btn svg { width: 15px; height: 15px; }
    .row-icon-btn:hover { color: var(--bleu); border-color: var(--bleu); }
    .row-icon-btn--danger:hover { color: var(--magenta); border-color: var(--magenta); }

    .msg-pagination { margin-top: 18px; }

    .empty-state {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        gap: 10px; padding: 48px 20px; color: var(--plomb);
    }
    .empty-state svg { width: 40px; height: 40px; opacity: .6; }
    .empty-state strong { color: inherit; font-size: 15px; }

    /* ==================== Lecture d'un message ==================== */
    .msg-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 20px; }
    .msg-meta__k {
        display: block; margin-bottom: 4px; font-size: 11.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em; color: var(--plomb);
    }
    .msg-meta__v { font-size: 14px; word-break: break-word; }
    .msg-meta__v a { color: var(--bleu); text-decoration: underline; }
    .msg-body {
        padding: 16px; font-size: 14.5px; line-height: 1.7;
        white-space: pre-wrap; word-break: break-word;
        background: var(--blanc-doux); border: 1px solid var(--glass-bord); border-radius: 12px;
    }

    /* ==================== Modale (fond flouté) ==================== */
    .modal-overlay {
        position: fixed; inset: 0;
        display: flex; align-items: flex-start; justify-content: center;
        padding: 40px 16px;
        background: color-mix(in srgb, var(--ink-2) 60%, transparent);
        backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        opacity: 0; visibility: hidden;
        transition: opacity .2s ease, visibility 0s linear .2s;
        z-index: 1000; overflow-y: auto;
    }
    .modal-overlay.is-open { opacity: 1; visibility: visible; transition: opacity .2s ease, visibility 0s linear 0s; }
    .modal-panel {
        width: 100%; max-width: 680px;
        transform: translateY(14px) scale(.98); opacity: 0;
        transition: transform .22s cubic-bezier(.2, .8, .2, 1), opacity .22s ease;
    }
    .modal-overlay.is-open .modal-panel { transform: translateY(0) scale(1); opacity: 1; }
    .modal-card {
        background: var(--ink-2); color: var(--blanc);
        border: 1px solid var(--glass-bord); border-radius: 20px;
        box-shadow: 0 24px 60px color-mix(in srgb, var(--ink-2) 80%, transparent);
        max-height: calc(100vh - 80px);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .modal-card__head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 20px 24px; border-bottom: 1px solid var(--glass-bord); flex-shrink: 0;
    }
    .modal-card__title { font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .modal-card__close {
        width: 34px; height: 34px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--glass-bord); background: transparent; color: var(--plomb);
        cursor: pointer; transition: border-color .15s ease, color .15s ease; flex-shrink: 0;
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
        .msg-meta { grid-template-columns: 1fr; }
    }

    /* ==================== Toast de notification ==================== */
    .toast {
        position: fixed; top: 24px; right: 24px; z-index: 1100;
        display: flex; align-items: center; gap: 10px;
        padding: 14px 18px; border-radius: 14px;
        font-size: 13.5px; font-weight: 600;
        box-shadow: 0 14px 34px color-mix(in srgb, var(--ink-2) 70%, transparent);
        background: var(--ink-2); border: 1px solid var(--glass-bord); color: var(--blanc);
        transform: translateY(-12px); opacity: 0; pointer-events: none;
        transition: transform .25s ease, opacity .25s ease;
    }
    .toast.is-visible { transform: translateY(0); opacity: 1; pointer-events: auto; }
    .toast--success { border-color: color-mix(in srgb, var(--vert) 45%, transparent); }
    .toast--success .toast__icon { color: var(--vert); }
    .toast__icon { display: flex; flex-shrink: 0; }
    .toast__icon svg { width: 18px; height: 18px; }
    .toast__close {
        margin-left: 6px; border: none; background: none; color: var(--plomb);
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

{{-- ===================== En-tête ===================== --}}
<div class="welcome">
    <div>
        <h1>Boîte aux lettres <span>✉️</span></h1>
        <p>Messages envoyés par les citoyens depuis le site.</p>
    </div>
</div>

{{-- ===================== Liste des messages ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="dash-sec-head">
            <div class="sec-head-icon">
                <div class="stat-icon" style="--stat-color:var(--magenta);">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                        <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                    </svg>
                </div>
                <div class="dash-sec-title">Messages reçus ({{ $total }})</div>
            </div>
        </div>

        <nav class="msg-tabs" aria-label="Filtrer les messages">
            <a href="{{ route('messages-citoyens') }}" class="msg-tab {{ $statut === null ? 'is-active' : '' }}">
                Tous <span class="msg-tab__count" id="tabCountAll">{{ $total }}</span>
            </a>
            <a href="{{ route('messages-citoyens', ['statut' => 'non-lus']) }}" class="msg-tab {{ $statut === 'non-lus' ? 'is-active' : '' }}">
                Non lus <span class="msg-tab__count" id="tabCountUnread">{{ $nonLus }}</span>
            </a>
            <a href="{{ route('messages-citoyens', ['statut' => 'lus']) }}" class="msg-tab {{ $statut === 'lus' ? 'is-active' : '' }}">
                Lus <span class="msg-tab__count" id="tabCountRead">{{ $total - $nonLus }}</span>
            </a>
        </nav>

        @if($messages->isNotEmpty())
            <div class="msg-table-wrap">
                <table class="msg-table">
                    <thead>
                        <tr>
                            <th class="msg-status-cell"><span class="visually-hidden">Statut</span></th>
                            <th>Objet</th>
                            <th>Expéditeur</th>
                            <th>Message</th>
                            <th>Reçu le</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $boite)
                            <tr class="msg-row {{ $boite->is_read ? '' : 'is-unread' }}"
                                data-id="{{ $boite->id }}"
                                data-read="{{ $boite->is_read ? '1' : '0' }}"
                                data-from="{{ $boite->name ?: 'Anonyme' }}">
                                <td class="msg-status-cell">
                                    <span class="msg-status" role="img"
                                          aria-label="{{ $boite->is_read ? 'Lu' : 'Non lu' }}"
                                          title="{{ $boite->is_read ? 'Lu' : 'Non lu' }}">
                                        {{-- Non lu : pastille --}}
                                        <svg class="msg-status__new" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="5" fill="currentColor"/>
                                        </svg>
                                        {{-- Lu : coche --}}
                                        <svg class="msg-status__read" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/>
                                        </svg>
                                    </span>
                                </td>
                                <td>
                                    <span class="msg-subject" style="--objet-color: {{ $couleursObjet[$boite->subject] ?? 'var(--plomb)' }};">{{ $boite->subject }}</span>
                                </td>
                                <td>
                                    <div class="msg-from">{{ $boite->name ?: 'Anonyme' }}</div>
                                    @if($boite->contact)
                                        <div class="msg-contact">{{ $boite->contact }}</div>
                                    @endif
                                </td>
                                <td class="msg-excerpt">{{ \Illuminate\Support\Str::limit($boite->message, 90) }}</td>
                                <td class="msg-date">{{ $boite->created_at->timezone('Indian/Antananarivo')->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button"
                                                class="row-icon-btn js-view-message"
                                                title="Lire"
                                                aria-label="Lire le message"
                                                data-message="{{ json_encode([
                                                    'id' => $boite->id,
                                                    'subject' => $boite->subject,
                                                    'name' => $boite->name ?: 'Anonyme',
                                                    'contact' => $boite->contact,
                                                    'message' => $boite->message,
                                                    'date' => $boite->created_at->timezone('Indian/Antananarivo')->format('d/m/Y H:i'),
                                                ]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <button type="button"
                                                class="row-icon-btn js-toggle-message"
                                                title="{{ $boite->is_read ? 'Marquer comme non lu' : 'Marquer comme lu' }}"
                                                aria-label="{{ $boite->is_read ? 'Marquer comme non lu' : 'Marquer comme lu' }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-10 5L2 7"/></svg>
                                        </button>
                                        <button type="button"
                                                class="row-icon-btn row-icon-btn--danger js-delete-message"
                                                title="Supprimer"
                                                aria-label="Supprimer le message">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 7"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($messages->hasPages())
                <div class="msg-pagination">{{ $messages->links() }}</div>
            @endif
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                @if($statut !== null)
                    <strong>Aucun message dans cette catégorie</strong>
                    <p>Essayez un autre filtre pour voir les autres messages.</p>
                @else
                    <strong>Aucun message pour le moment</strong>
                    <p>Les messages envoyés depuis le formulaire public apparaîtront ici.</p>
                @endif
            </div>
        @endif
    </div>
</section>

{{-- ===================== Modale : lecture d'un message ===================== --}}
<div class="modal-overlay" id="messageModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>✉️</span>
                    <span>Message reçu</span>
                </div>
                <button type="button" class="modal-card__close" id="closeReadBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <div class="msg-meta">
                    <div><span class="msg-meta__k">Objet</span><span class="msg-meta__v" id="readSubject"></span></div>
                    <div><span class="msg-meta__k">Reçu le</span><span class="msg-meta__v" id="readDate"></span></div>
                    <div><span class="msg-meta__k">Expéditeur</span><span class="msg-meta__v" id="readFrom"></span></div>
                    <div><span class="msg-meta__k">Contact</span><span class="msg-meta__v" id="readContact"></span></div>
                </div>

                <div class="msg-body" id="readBody"></div>

                <div class="editor-actions">
                    <button type="button" class="btn btn--ghost" id="readToggleBtn">Marquer comme non lu</button>
                    <button type="button" class="btn btn--danger" id="readDeleteBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : confirmation de suppression ===================== --}}
<div class="modal-overlay" id="messageDeleteModalOverlay">
    <div class="modal-panel" style="max-width: 420px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer le message</span>
                </div>
                <button type="button" class="modal-card__close" id="closeDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p style="font-size:14px; line-height:1.6; color: var(--plomb); margin: 0 0 20px;">
                    Supprimer définitivement le message de « <strong id="deleteMessageFrom" style="color: inherit;"></strong> » ? Cette action est irréversible.
                </p>
                <form action="" method="POST" id="messageDeleteForm" style="display:flex; gap:10px;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn--ghost" id="cancelDeleteBtn" style="flex:1;">Annuler</button>
                    <button type="submit" class="btn btn--danger" style="flex:1;">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const csrf = @json(csrf_token());
        const toggleUrlTemplate  = @json(route('messages-citoyens.toggle', '__ID__'));
        const destroyUrlTemplate = @json(route('messages-citoyens.destroy', '__ID__'));
        const total = @json($total);
        let unread  = @json($nonLus);

        const readOverlay   = document.getElementById('messageModalOverlay');
        const deleteOverlay = document.getElementById('messageDeleteModalOverlay');
        const deleteForm    = document.getElementById('messageDeleteForm');
        const readToggleBtn = document.getElementById('readToggleBtn');

        const tabAll    = document.getElementById('tabCountAll');
        const tabUnread = document.getElementById('tabCountUnread');
        const tabRead   = document.getElementById('tabCountRead');

        let currentRow = null;

        function lockScroll(lock) {
            document.body.classList.toggle('modal-lock', lock);
        }

        // ---------- Compteurs et état lu / non lu ----------
        function refreshTabs() {
            if (tabAll)    tabAll.textContent    = total;
            if (tabUnread) tabUnread.textContent = unread;
            if (tabRead)   tabRead.textContent   = total - unread;
        }

        function syncReadToggleLabel() {
            if (!currentRow) return;
            readToggleBtn.textContent = currentRow.dataset.read === '1'
                ? 'Marquer comme non lu'
                : 'Marquer comme lu';
        }

        function setReadState(row, isRead) {
            const wasRead = row.dataset.read === '1';
            if (wasRead === isRead) return;

            row.dataset.read = isRead ? '1' : '0';
            row.classList.toggle('is-unread', !isRead);
            unread += isRead ? -1 : 1;

            // Pastille / coche de statut (l'affichage suit la classe is-unread, on met à jour le texte)
            const status = row.querySelector('.msg-status');
            if (status) {
                const statusLabel = isRead ? 'Lu' : 'Non lu';
                status.title = statusLabel;
                status.setAttribute('aria-label', statusLabel);
            }

            const btn = row.querySelector('.js-toggle-message');
            const label = isRead ? 'Marquer comme non lu' : 'Marquer comme lu';
            btn.title = label;
            btn.setAttribute('aria-label', label);

            refreshTabs();
            if (row === currentRow) syncReadToggleLabel();
        }

        async function toggleRemote(row) {
            const res = await fetch(toggleUrlTemplate.replace('__ID__', row.dataset.id), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('toggle failed');
            const data = await res.json();
            setReadState(row, !!data.is_read);
        }

        // ---------- Contact : lien e-mail / téléphone si le format est reconnu ----------
        function renderContact(container, contact) {
            container.textContent = '';
            if (!contact) { container.textContent = '—'; return; }

            const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRe = /^\+?[\d\s().-]{6,}$/;

            if (emailRe.test(contact)) {
                const a = document.createElement('a');
                a.href = 'mailto:' + contact;
                a.textContent = contact;
                container.appendChild(a);
            } else if (phoneRe.test(contact)) {
                const a = document.createElement('a');
                a.href = 'tel:' + contact.replace(/[^\d+]/g, '');
                a.textContent = contact;
                container.appendChild(a);
            } else {
                container.textContent = contact;
            }
        }

        // ---------- Modale de lecture ----------
        function openRead(row) {
            let data;
            try {
                data = JSON.parse(row.querySelector('.js-view-message').getAttribute('data-message'));
            } catch (e) {
                console.error('Impossible de lire le message', e);
                return;
            }

            currentRow = row;
            // textContent partout : le contenu des citoyens n'est jamais interprété comme du HTML
            document.getElementById('readSubject').textContent = data.subject;
            document.getElementById('readDate').textContent    = data.date;
            document.getElementById('readFrom').textContent    = data.name;
            document.getElementById('readBody').textContent    = data.message;
            renderContact(document.getElementById('readContact'), data.contact);

            syncReadToggleLabel();
            readOverlay.classList.add('is-open');
            lockScroll(true);

            // Ouvrir un message non lu le marque automatiquement comme lu
            if (row.dataset.read === '0') {
                toggleRemote(row).catch(function () {});
            }
        }

        function closeRead() {
            readOverlay.classList.remove('is-open');
            lockScroll(false);
        }

        // ---------- Modale de suppression ----------
        function openDelete(row) {
            deleteForm.action = destroyUrlTemplate.replace('__ID__', row.dataset.id);
            document.getElementById('deleteMessageFrom').textContent = row.dataset.from;
            deleteOverlay.classList.add('is-open');
            lockScroll(true);
        }

        function closeDelete() {
            deleteOverlay.classList.remove('is-open');
            lockScroll(false);
        }

        // ---------- Branchements ----------
        document.querySelectorAll('.msg-row').forEach(function (row) {
            row.addEventListener('click', function (e) {
                if (e.target.closest('.row-actions')) return;
                openRead(row);
            });

            row.querySelector('.js-view-message').addEventListener('click', function () {
                openRead(row);
            });

            row.querySelector('.js-toggle-message').addEventListener('click', function () {
                currentRow = row;
                toggleRemote(row).catch(function () {});
            });

            row.querySelector('.js-delete-message').addEventListener('click', function () {
                currentRow = row;
                openDelete(row);
            });
        });

        readToggleBtn.addEventListener('click', function () {
            if (currentRow) toggleRemote(currentRow).catch(function () {});
        });

        document.getElementById('readDeleteBtn').addEventListener('click', function () {
            if (!currentRow) return;
            closeRead();
            openDelete(currentRow);
        });

        document.getElementById('closeReadBtn').addEventListener('click', closeRead);
        document.getElementById('closeDeleteModalBtn').addEventListener('click', closeDelete);
        document.getElementById('cancelDeleteBtn').addEventListener('click', closeDelete);

        readOverlay.addEventListener('click', function (e) { if (e.target === readOverlay) closeRead(); });
        deleteOverlay.addEventListener('click', function (e) { if (e.target === deleteOverlay) closeDelete(); });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (deleteOverlay.classList.contains('is-open')) closeDelete();
            else if (readOverlay.classList.contains('is-open')) closeRead();
        });

        // ---------- Toast de notification (auto-disparition) ----------
        const pageToast = document.getElementById('pageToast');
        if (pageToast) {
            const pageToastClose = document.getElementById('pageToastClose');
            requestAnimationFrame(function () {
                pageToast.classList.add('is-visible');
            });

            const toastTimer = setTimeout(hideToast, 5000);

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
    })();
</script>
@endpush