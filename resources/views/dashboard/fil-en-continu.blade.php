@extends('layouts.dashboard-layout')

@section('title', "Fil en continu - SIOKA")

@section('content')

<style>
    /* ==================== Formulaire (même esprit que .fil-form) ==================== */
    .fil-form .field { margin-bottom: 18px; }
    .fil-form .field:last-child { margin-bottom: 0; }

    .fil-form label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }
    .fil-form .req { color: var(--magenta); font-weight: 700; }

    .fil-form select,
    .fil-form textarea {
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
    .fil-form textarea { resize: vertical; line-height: 1.6; }
    .fil-form select option {
        background: var(--ink-2);
        color: var(--blanc);
    }
    .fil-form select:focus,
    .fil-form textarea:focus {
        outline: none;
        border-color: var(--bleu);
        box-shadow: 0 0 0 3px var(--bleu-glow);
    }
    .fil-form .has-error { border-color: var(--magenta) !important; }

    .field-hint { margin: 6px 0 0; font-size: 12px; color: var(--plomb); }
    .field-error { margin: 6px 0 0; font-size: 12px; color: var(--magenta); font-weight: 600; }
    .char-counter { font-size: 11.5px; color: var(--plomb); }
    .char-counter.is-over { color: var(--magenta); font-weight: 700; }

    .page-head-actions { display: flex; align-items: center; gap: 12px; }

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

    /* ==================== Liste des fils en continu ==================== */
    .fil-table-wrap { overflow-x: auto; }
    .fil-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .fil-table th {
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
    .fil-table td {
        padding: 12px;
        border-bottom: 1px solid var(--glass-bord);
        vertical-align: middle;
    }
    .fil-table tbody tr { transition: background-color .15s ease; }
    .fil-table tbody tr:hover { background: var(--blanc-doux); }
    .fil-table tbody tr:last-child td { border-bottom: none; }

    .fil-content { max-width: 380px; line-height: 1.4; }
    .pill-category {
        display: inline-flex; align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px; font-weight: 600;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
        white-space: nowrap;
    }
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
    .row-icon-btn:hover { color: var(--vert); border-color: var(--vert); }
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

    /* ==================== Modale (identique à celle des articles/flash-infos) ==================== */
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
        width: 100%; max-width: 520px;
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
        .modal-overlay { padding: 0; align-items: stretch; }
        .modal-panel { max-width: none; height: 100%; }
        .modal-card { height: 100%; max-height: none; border-radius: 0; }
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

{{-- ===================== En-tête, dans le style de la vue d'ensemble ===================== --}}
<div class="welcome">
    <div>
        <h1>Fil en continu <span>🕒</span></h1>
        <p>Gérez le fil d'actualités en continu affiché sur le site SIOKA.</p>
    </div>
    <div class="page-head-actions">
        <button type="button" class="btn btn--primary" id="openFilCreateModalBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Nouveau fil
        </button>
    </div>
</div>

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

{{-- ===================== Liste des fils en continu ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="dash-sec-head">
            <div class="sec-head-icon">
                <div class="stat-icon" style="--stat-color:var(--bleu);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="dash-sec-title">Fil en continu ({{ $fils->total() }})</div>
            </div>
        </div>

        @if($fils->count() > 0)
            @if($fils->hasPages())
                <div class="pagination-wrap pagination-wrap--top">{{ $fils->links() }}</div>
            @endif

            <div class="fil-table-wrap">
                <table class="fil-table">
                    <thead>
                        <tr>
                            <th>Contenu</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fils as $fil)
                            <tr>
                                <td class="fil-content">{{ $fil->content }}</td>
                                <td><span class="pill-category">{{ $fil->category->name ?? '—' }}</span></td>
                                <td>{{ $fil->user->name ?? '—' }}</td>
                                <td>
                                    <span class="status-badge {{ $fil->is_active ? 'is-live' : '' }}">
                                        <span class="status-dot" style="background:{{ $fil->is_active ? 'var(--vert)' : 'var(--plomb)' }}"></span>
                                        {{ $fil->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="row-date">{{ $fil->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <form action="{{ route('fil-en-continu.toggle', $fil) }}" method="POST" data-turbo="false">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="row-icon-btn" title="{{ $fil->is_active ? 'Désactiver' : 'Activer' }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 2"/></svg>
                                            </button>
                                        </form>
                                        <button type="button"
                                            class="row-icon-btn"
                                            title="Modifier"
                                            data-edit-btn
                                            data-action="{{ route('fil-en-continu.update', $fil) }}"
                                            data-category="{{ $fil->category_id }}"
                                            data-content="{{ $fil->content }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21H3v-4L16.5 3.5Z"/></svg>
                                        </button>
                                        <button type="button"
                                            class="row-icon-btn row-icon-btn--danger"
                                            title="Supprimer"
                                            data-delete-btn
                                            data-action="{{ route('fil-en-continu.destroy', $fil) }}">
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <strong>Aucun fil pour le moment</strong>
                <p>Créez votre premier fil pour qu'il apparaisse dans le bloc « En continu » du site.</p>
            </div>
        @endif
    </div>
</section>

{{-- ===================== Modale : formulaire de création ===================== --}}
<div class="modal-overlay" id="filModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🕒</span>
                    <span>Nouveau fil</span>
                </div>
                <button type="button" class="modal-card__close" id="closeFilModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="{{ route('fil-en-continu.store') }}" method="POST" class="fil-form" data-turbo="false">
                    @csrf

                    <div class="field">
                        <label>Catégorie</label>
                        <select name="category_id" class="{{ $errors->has('category_id') ? 'has-error' : '' }}">
                            <option value="" {{ old('category_id') ? '' : 'selected' }}>Aucune catégorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Contenu <span class="req">*</span></label>
                        <textarea
                            name="content"
                            id="filContent"
                            maxlength="280"
                            rows="3"
                            class="{{ $errors->has('content') ? 'has-error' : '' }}"
                            placeholder="Écrivez l'actualité à diffuser dans le fil en continu du site..."
                            required>{{ old('content') }}</textarea>
                        <div style="display:flex; justify-content:space-between; margin-top:6px;">
                            @error('content')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                            <span class="char-counter" id="filCharCounter" style="margin-left:auto;">0 / 280</span>
                        </div>
                    </div>

                    <div style="margin-top:18px;">
                        <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;">
                            Publier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : formulaire de modification ===================== --}}
<div class="modal-overlay" id="filEditModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>✏️</span>
                    <span>Modifier le fil</span>
                </div>
                <button type="button" class="modal-card__close" id="closeFilEditModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="" method="POST" class="fil-form" id="filEditForm" data-turbo="false">
                    @csrf
                    @method('PUT')

                    <div class="field">
                        <label>Catégorie</label>
                        <select name="category_id" id="editFilCategoryId">
                            <option value="">Aucune catégorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Contenu <span class="req">*</span></label>
                        <textarea
                            name="content"
                            id="editFilContent"
                            maxlength="280"
                            rows="3"
                            placeholder="Écrivez l'actualité à diffuser dans le fil en continu du site..."
                            required></textarea>
                        <div style="display:flex; justify-content:flex-end; margin-top:6px;">
                            <span class="char-counter" id="editFilCharCounter">0 / 280</span>
                        </div>
                    </div>

                    <div style="margin-top:18px;">
                        <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : confirmation de suppression ===================== --}}
<div class="modal-overlay" id="filDeleteModalOverlay">
    <div class="modal-panel" style="max-width: 420px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer le fil</span>
                </div>
                <button type="button" class="modal-card__close" id="closeFilDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p style="font-size:14px; line-height:1.6; color: var(--plomb); margin: 0 0 20px;">
                    Cette action est définitive. Le fil sera supprimé du bloc « En continu » du site et ne pourra pas être récupéré.
                </p>
                <form action="" method="POST" id="filDeleteForm" data-turbo="false" style="display:flex; gap:10px;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn" id="cancelFilDeleteBtn" style="flex:1; justify-content:center; border-color: var(--glass-bord);">
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
    document.addEventListener('turbo:load', function () {
        const overlay = document.getElementById('filModalOverlay');
        const openBtn = document.getElementById('openFilCreateModalBtn');
        const closeBtn = document.getElementById('closeFilModalBtn');
        if (!overlay || !openBtn) return;

        function openModal() {
            overlay.classList.add('is-open');
            document.body.classList.add('modal-lock');
        }
        function closeModal() {
            overlay.classList.remove('is-open');
            document.body.classList.remove('modal-lock');
        }

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
        });

        // Compteur de caractères
        const textarea = document.getElementById('filContent');
        const counter = document.getElementById('filCharCounter');
        function updateCounter() {
            const len = textarea.value.length;
            counter.textContent = len + ' / 280';
            counter.classList.toggle('is-over', len >= 280);
        }
        if (textarea && counter) {
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }

        // Réouverture automatique après erreur de validation
        @if($errors->any())
            openModal();
        @endif

        // ---------- Toast de notification (auto-disparition) ----------
        const pageToast = document.getElementById('pageToast');
        if (pageToast) {
            const pageToastClose = document.getElementById('pageToastClose');
            requestAnimationFrame(function () {
                pageToast.classList.add('is-visible');
            });

            function hideToast() {
                pageToast.classList.remove('is-visible');
            }

            let toastTimer = setTimeout(hideToast, 5000);

            if (pageToastClose) {
                pageToastClose.addEventListener('click', function () {
                    clearTimeout(toastTimer);
                    hideToast();
                });
            }
        }

        // ---------- Modale de modification ----------
        const editOverlay = document.getElementById('filEditModalOverlay');
        const closeEditBtn = document.getElementById('closeFilEditModalBtn');
        const editForm = document.getElementById('filEditForm');
        const editCategorySelect = document.getElementById('editFilCategoryId');
        const editTextarea = document.getElementById('editFilContent');
        const editCounter = document.getElementById('editFilCharCounter');

        function updateEditCounter() {
            const len = editTextarea.value.length;
            editCounter.textContent = len + ' / 280';
            editCounter.classList.toggle('is-over', len >= 280);
        }
        if (editTextarea && editCounter) {
            editTextarea.addEventListener('input', updateEditCounter);
        }

        function openEditModal() {
            editOverlay.classList.add('is-open');
            document.body.classList.add('modal-lock');
        }
        function closeEditModal() {
            editOverlay.classList.remove('is-open');
            document.body.classList.remove('modal-lock');
        }

        document.querySelectorAll('[data-edit-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                editForm.action = btn.dataset.action;
                editCategorySelect.value = btn.dataset.category;
                editTextarea.value = btn.dataset.content;
                updateEditCounter();
                openEditModal();
            });
        });

        if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditModal);
        if (editOverlay) {
            editOverlay.addEventListener('click', function (e) {
                if (e.target === editOverlay) closeEditModal();
            });
        }

        // ---------- Modale de confirmation de suppression ----------
        const deleteOverlay = document.getElementById('filDeleteModalOverlay');
        const closeDeleteBtn = document.getElementById('closeFilDeleteModalBtn');
        const cancelDeleteBtn = document.getElementById('cancelFilDeleteBtn');
        const deleteForm = document.getElementById('filDeleteForm');

        function openDeleteModal() {
            deleteOverlay.classList.add('is-open');
            document.body.classList.add('modal-lock');
        }
        function closeDeleteModal() {
            deleteOverlay.classList.remove('is-open');
            document.body.classList.remove('modal-lock');
        }

        document.querySelectorAll('[data-delete-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                deleteForm.action = btn.dataset.action;
                openDeleteModal();
            });
        });

        if (closeDeleteBtn) closeDeleteBtn.addEventListener('click', closeDeleteModal);
        if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        if (deleteOverlay) {
            deleteOverlay.addEventListener('click', function (e) {
                if (e.target === deleteOverlay) closeDeleteModal();
            });
        }

        // Échap : ferme la modale actuellement ouverte (création, édition ou suppression)
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (editOverlay.classList.contains('is-open')) closeEditModal();
            if (deleteOverlay.classList.contains('is-open')) closeDeleteModal();
        });
    });
</script>
@endpush