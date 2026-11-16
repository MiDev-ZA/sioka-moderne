@extends('layouts.dashboard-layout')

@section('title', "Abonnés newsletter - SIOKA")

@section('content')

<style>
    /* ==================== En-tête de section (mise en page propre, indépendante du reste) ==================== */
    .nl-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px 16px;
        margin-bottom: 18px;
    }
    .nl-head__title { display: flex; align-items: center; gap: 12px; }

    /* ==================== Recherche instantanée ==================== */
    .nl-search { position: relative; margin-left: auto; width: 300px; max-width: 100%; }
    .nl-search__icon {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        width: 15px; height: 15px; color: var(--plomb); pointer-events: none;
    }
    .nl-search input[type="search"] {
        width: 100%;
        padding: 10px 38px 10px 36px;
        font: inherit;
        font-size: 14px;
        color: inherit;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
        border-radius: 12px;
        -webkit-appearance: none;
        appearance: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .nl-search input[type="search"]::-webkit-search-cancel-button { -webkit-appearance: none; }
    .nl-search input[type="search"]::placeholder { color: var(--plomb); }
    .nl-search input[type="search"]:focus {
        outline: none;
        border-color: var(--bleu);
        box-shadow: 0 0 0 3px var(--bleu-glow);
    }
    .nl-search__clear {
        position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        width: 24px; height: 24px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border: none; border-radius: 8px; background: none;
        color: var(--plomb); cursor: pointer;
        transition: color .15s ease;
    }
    .nl-search__clear svg { width: 13px; height: 13px; }
    .nl-search__clear:hover { color: var(--magenta); }
    .nl-search__clear[hidden] { display: none; }

    .nl-meta { margin: 0 0 14px; font-size: 12px; color: var(--plomb); }
    .nl-results { transition: opacity .15s ease; }
    .nl-results.is-loading { opacity: .5; pointer-events: none; }

    /* ==================== Boutons ==================== */
    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px; font-size: 13.5px; font-weight: 600;
        border-radius: 999px; border: 1px solid transparent;
        cursor: pointer; text-decoration: none; line-height: 1.2;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, color .15s ease;
        background: none; color: inherit;
    }
    .btn:active { transform: translateY(1px); }
    .btn:disabled { opacity: .6; cursor: not-allowed; }
    .btn--danger { background: var(--magenta); color: #fff; }

    /* ==================== Liste des abonnés ==================== */
    .nl-table-wrap { overflow-x: auto; }
    .nl-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .nl-table th {
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
    .nl-table td {
        padding: 12px;
        border-bottom: 1px solid var(--glass-bord);
        vertical-align: middle;
    }
    .nl-table tbody tr { transition: background-color .15s ease; }
    .nl-table tbody tr:hover { background: var(--blanc-doux); }
    .nl-table tbody tr:last-child td { border-bottom: none; }

    .nl-col-num { width: 56px; color: var(--plomb); }
    .nl-email { font-weight: 600; word-break: break-all; }
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
    .row-icon-btn--danger:hover { color: var(--magenta); border-color: var(--magenta); }

    .empty-state {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        gap: 10px; padding: 48px 20px;
        color: var(--plomb);
    }
    .empty-state svg { width: 40px; height: 40px; opacity: .6; }
    .empty-state strong { color: inherit; font-size: 15px; }

    .pagination-wrap { margin-top: 18px; }
    .pagination-wrap--top { margin-top: 0; margin-bottom: 18px; }

    /* ==================== Modale (identique à celle des fils / articles) ==================== */
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
    .toast--error { border-color: var(--magenta); }
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
        .nl-search { width: 100%; margin-left: 0; }
    }
</style>

{{-- ===================== En-tête, dans le style de la vue d'ensemble ===================== --}}
<div class="welcome">
    <div>
        <h1>Abonnés newsletter <span>📬</span></h1>
        <p>Consultez et gérez les adresses e-mail inscrites à la newsletter du site SIOKA.</p>
    </div>
</div>

{{-- ===================== Liste des abonnés ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="nl-head">
            <div class="nl-head__title">
                <div class="stat-icon" style="--stat-color:var(--bleu);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6"/></svg>
                </div>
                <div class="dash-sec-title" id="nlTitle">Abonnés ({{ $total }})</div>
            </div>

            <form class="nl-search" id="nlSearchForm" method="GET" action="{{ route('abonnees-newsletter') }}" role="search" data-turbo="false">
                <svg class="nl-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                <input type="search" id="nlSearchInput" name="q" value="{{ $q }}"
                       placeholder="Rechercher un e-mail…" autocomplete="off" aria-label="Rechercher un e-mail">
                <button type="button" class="nl-search__clear" id="nlSearchClear" aria-label="Effacer la recherche" @if ($q === '') hidden @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </form>
        </div>

        <div class="nl-results" id="nlResults">
            @include('dashboard.partials.abonnees-results')
        </div>
    </div>
</section>

{{-- ===================== Modale : confirmation de suppression ===================== --}}
<div class="modal-overlay" id="nlDeleteModalOverlay">
    <div class="modal-panel" style="max-width: 420px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer l'abonné</span>
                </div>
                <button type="button" class="modal-card__close" id="closeNlDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p style="font-size:14px; line-height:1.6; color: var(--plomb); margin: 0 0 20px;">
                    Cette action est définitive. L'adresse <strong id="nlDeleteEmail" style="color: inherit; word-break: break-all;"></strong>
                    sera retirée de la liste de diffusion et ne pourra pas être récupérée.
                </p>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn" id="cancelNlDeleteBtn" style="flex:1; justify-content:center; border-color: var(--glass-bord);">
                        Annuler
                    </button>
                    <button type="button" class="btn btn--danger" id="confirmNlDeleteBtn" style="flex:1; justify-content:center;">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('turbo:load', function () {
        const box      = document.getElementById('nlResults');
        const form     = document.getElementById('nlSearchForm');
        const input    = document.getElementById('nlSearchInput');
        const clearBtn = document.getElementById('nlSearchClear');
        const titleEl  = document.getElementById('nlTitle');
        if (!box || !form || !input) return;

        // Évite de lier deux fois les événements si Turbo ré-exécute le script
        if (box.dataset.ready) return;
        box.dataset.ready = '1';

        const base    = @json(route('abonnees-newsletter'));
        const csrf    = @json(csrf_token());
        const HEADERS = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

        let page = {{ $abonnes->currentPage() }};
        let ctrl = null, searchTimer = null, toastTimer = null;

        // ---------- Toast de notification (même rendu que les autres pages) ----------
        const ICONS = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>',
            error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v5M12 16.5v.01"/></svg>'
        };
        const ICON_CLOSE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>';

        function hideToast(toast) {
            toast.classList.remove('is-visible');
            setTimeout(function () { toast.remove(); }, 300);
        }

        function showToast(message, type) {
            const previous = document.getElementById('pageToast');
            if (previous) previous.remove();
            clearTimeout(toastTimer);

            const toast = document.createElement('div');
            toast.id = 'pageToast';
            toast.className = 'toast toast--' + type;
            toast.innerHTML =
                '<span class="toast__icon">' + ICONS[type] + '</span>' +
                '<span class="toast__msg"></span>' +
                '<button type="button" class="toast__close" aria-label="Fermer">' + ICON_CLOSE + '</button>';
            toast.querySelector('.toast__msg').textContent = message;
            document.body.appendChild(toast);

            requestAnimationFrame(function () { toast.classList.add('is-visible'); });

            toast.querySelector('.toast__close').addEventListener('click', function () {
                clearTimeout(toastTimer);
                hideToast(toast);
            });
            toastTimer = setTimeout(function () { hideToast(toast); }, 5000);
        }

        // ---------- Chargement de la liste (le champ de recherche n'est jamais remplacé) ----------
        function buildUrl(p) {
            const params = new URLSearchParams();
            const q = input.value.trim();
            if (q) params.set('q', q);
            if (p > 1) params.set('page', p);
            const s = params.toString();
            return base + (s ? '?' + s : '');
        }

        async function load(p) {
            if (ctrl) ctrl.abort();
            const mine = ctrl = new AbortController();
            box.classList.add('is-loading');

            try {
                const res = await fetch(buildUrl(p), { headers: HEADERS, signal: mine.signal });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();

                box.innerHTML = data.html;
                titleEl.textContent = 'Abonnés (' + data.total + ')';
                page = data.page;
                history.replaceState(history.state, '', buildUrl(page));   // conserve l'état Turbo
            } catch (err) {
                if (err.name !== 'AbortError') showToast('Chargement impossible. Réessayez.', 'error');
            } finally {
                if (ctrl === mine) box.classList.remove('is-loading');
            }
        }

        // ---------- Recherche instantanée (250 ms après la dernière frappe) ----------
        input.addEventListener('input', function () {
            clearBtn.hidden = input.value === '';
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { load(1); }, 250);
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && input.value !== '') clearBtn.click();
        });

        form.addEventListener('submit', function (e) {   // touche Entrée
            e.preventDefault();
            clearTimeout(searchTimer);
            load(1);
        });

        clearBtn.addEventListener('click', function () {
            input.value = '';
            clearBtn.hidden = true;
            input.focus();
            clearTimeout(searchTimer);
            load(1);
        });

        // ---------- Modale de confirmation de suppression ----------
        const deleteOverlay    = document.getElementById('nlDeleteModalOverlay');
        const closeDeleteBtn   = document.getElementById('closeNlDeleteModalBtn');
        const cancelDeleteBtn  = document.getElementById('cancelNlDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmNlDeleteBtn');
        const deleteEmail      = document.getElementById('nlDeleteEmail');
        let pendingUrl = null;

        function openDeleteModal() {
            deleteOverlay.classList.add('is-open');
            document.body.classList.add('modal-lock');
        }
        function closeDeleteModal() {
            deleteOverlay.classList.remove('is-open');
            document.body.classList.remove('modal-lock');
            pendingUrl = null;
        }

        closeDeleteBtn.addEventListener('click', closeDeleteModal);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        deleteOverlay.addEventListener('click', function (e) {
            if (e.target === deleteOverlay) closeDeleteModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && deleteOverlay.classList.contains('is-open')) closeDeleteModal();
        });

        confirmDeleteBtn.addEventListener('click', async function () {
            if (!pendingUrl) return;

            confirmDeleteBtn.disabled = true;
            cancelDeleteBtn.disabled = true;

            try {
                const res = await fetch(pendingUrl, {
                    method: 'DELETE',
                    headers: Object.assign({ 'X-CSRF-TOKEN': csrf }, HEADERS)
                });
                if (!res.ok && res.status !== 404) throw new Error(res.status);

                closeDeleteModal();
                showToast('Abonné supprimé avec succès.', 'success');
                await load(page);
            } catch (err) {
                closeDeleteModal();
                showToast(err.message === '419'
                    ? 'Session expirée, rechargez la page.'
                    : 'Suppression impossible. Réessayez.', 'error');
            } finally {
                confirmDeleteBtn.disabled = false;
                cancelDeleteBtn.disabled = false;
            }
        });

        // ---------- Délégation : suppression + pagination (la liste est remplacée dynamiquement) ----------
        box.addEventListener('click', function (e) {
            const link = e.target.closest('.pagination-wrap a[href]');
            if (link) {
                e.preventDefault();
                load(Number(new URL(link.href).searchParams.get('page') || 1));
                return;
            }

            const del = e.target.closest('[data-delete-btn]');
            if (del) {
                pendingUrl = del.dataset.action;
                deleteEmail.textContent = del.dataset.email;
                openDeleteModal();
            }
        });
    });
</script>
@endpush