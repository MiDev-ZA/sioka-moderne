@extends('layouts.dashboard-layout')

@section('title', "Utilisateurs - SIOKA")

@section('content')

@php
    // Libellés des rôles (valeurs stockées dans users.role)
    $roles = [
        'admin'      => 'Administrateur',
        'editor'     => 'Rédacteur en chef',
        'journalist' => 'Journaliste',
        'producer'   => 'Producteur / Animateur',
        'integrator' => 'Intégrateur vidéos & contenus',
        'user'       => 'Membre',
    ];

    // Description affichée sous le menu des rôles
    $roleDescriptions = [
        'admin'      => 'Accès complet : utilisateurs, connexion YouTube et tous les contenus.',
        'editor'     => 'Supervise et publie tous les contenus : actualités, flash infos, émissions, podcasts, newsletter.',
        'journalist' => "Rédige les actualités, les flash infos et le fil d'infos.",
        'producer'   => 'Gère les émissions et les podcasts.',
        'integrator' => 'Gère la WebTV : intègre et publie les vidéos et contenus.',
        'user'       => "Compte public : aucun accès aux outils de la rédaction.",
    ];

    // Vrai si la dernière soumission en erreur venait de la modale de modification
    $isEditError = old('_form') === 'edit';

    // Message d'erreur pour la modale de création (null si l'erreur vient de la modification)
    $createError = fn ($field) => (! $isEditError && $errors->has($field)) ? $errors->first($field) : null;
    // Message d'erreur pour la modale de modification
    $editError   = fn ($field) => ($isEditError && $errors->has($field)) ? $errors->first($field) : null;
@endphp

<style>
    /* ==================== Formulaire (même esprit que .flash-form) ==================== */
    .user-form .field { margin-bottom: 18px; }
    .user-form .field:last-child { margin-bottom: 0; }
    .user-form .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 560px) { .user-form .field-row { grid-template-columns: 1fr; } }

    .user-form label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }
    .user-form .req { color: var(--magenta); font-weight: 700; }

    .user-form input,
    .user-form select {
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
    .user-form select option {
        background: var(--ink-2);
        color: var(--blanc);
    }
    .user-form input:focus,
    .user-form select:focus {
        outline: none;
        border-color: var(--bleu);
        box-shadow: 0 0 0 3px var(--bleu-glow);
    }
    .user-form .has-error { border-color: var(--magenta) !important; }

    .field-hint { margin: 6px 0 0; font-size: 12px; color: var(--plomb); }
    .field-error { margin: 6px 0 0; font-size: 12px; color: var(--magenta); font-weight: 600; }

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

    /* ==================== Liste des utilisateurs ==================== */
    .user-table-wrap { overflow-x: auto; }
    .user-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .user-table th {
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
    .user-table td {
        padding: 12px;
        border-bottom: 1px solid var(--glass-bord);
        vertical-align: middle;
    }
    .user-table tbody tr { transition: background-color .15s ease; }
    .user-table tbody tr:hover { background: var(--blanc-doux); }
    .user-table tbody tr:last-child td { border-bottom: none; }

    .user-cell { display: flex; align-items: center; gap: 12px; }
    .user-avatar {
        width: 34px; height: 34px; flex-shrink: 0;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, var(--magenta), var(--bleu));
        text-transform: uppercase;
    }
    .user-avatar { overflow: hidden; }
    .user-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .user-name { font-weight: 600; line-height: 1.3; }

    /* Champ photo de profil */
    .avatar-field { display: flex; align-items: center; gap: 14px; }
    .avatar-field__input { flex: 1; min-width: 0; }
    .avatar-preview {
        width: 64px; height: 64px; flex-shrink: 0;
        border-radius: 50%; overflow: hidden;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase;
        background: linear-gradient(135deg, var(--magenta), var(--bleu));
    }
    .avatar-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .user-form input[type="file"] { padding: 8px 12px; font-size: 13px; }
    .user-form .check-line {
        display: flex; align-items: center; gap: 8px;
        margin: 8px 0 0; font-size: 12.5px; font-weight: 500; color: var(--plomb); cursor: pointer;
    }
    .user-form input[type="checkbox"] { width: auto; padding: 0; accent-color: var(--magenta); }
    .user-you { font-size: 11px; font-weight: 600; color: var(--plomb); margin-left: 6px; }
    .user-email { color: var(--plomb); }
    .row-date { color: var(--plomb); white-space: nowrap; }

    /* Badge de rôle */
    .role-pill {
        display: inline-flex; align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px; font-weight: 600;
        background: var(--blanc-doux);
        border: 1px solid var(--glass-bord);
        color: var(--plomb);
        white-space: nowrap;
    }
    .role-pill.is-admin      { color: var(--magenta); border-color: rgba(179, 25, 131, .35); }
    .role-pill.is-editor     { color: var(--or);      border-color: rgba(201, 162, 39, .40); }
    .role-pill.is-journalist { color: var(--bleu);    border-color: rgba(0, 130, 202, .35); }
    .role-pill.is-producer   { color: var(--vert);    border-color: rgba(47, 191, 113, .35); }

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
    .row-icon-btn:disabled { opacity: .4; cursor: not-allowed; }
    .row-icon-btn:disabled:hover { color: var(--plomb); border-color: var(--glass-bord); }

    .empty-state {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        gap: 10px; padding: 48px 20px;
        color: var(--plomb);
    }
    .empty-state svg { width: 40px; height: 40px; opacity: .6; }
    .empty-state strong { color: inherit; font-size: 15px; }

    .pagination-wrap { margin-top: 18px; }
    .pagination-wrap--top { margin-top: 0; margin-bottom: 18px; }

    /* ==================== Modale (identique à celle des flash infos) ==================== */
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
        width: 100%; max-width: 560px;
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
        <h1>Utilisateurs <span>👥</span></h1>
        <p>Créez et gérez les comptes ayant accès au tableau de bord SIOKA.</p>
    </div>
    <div class="page-head-actions">
        <button type="button" class="btn btn--primary" id="openCreateModalBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Nouvel utilisateur
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

{{-- ===================== Liste des utilisateurs ===================== --}}
<section class="panel">
    <div class="panel-pad">
        <div class="dash-sec-head">
            <div class="sec-head-icon">
                <div class="stat-icon" style="--stat-color:var(--bleu);">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM21 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <div class="dash-sec-title">Utilisateurs ({{ $users->total() }})</div>
            </div>
        </div>

        @if($users->count() > 0)
            @if($users->hasPages())
                <div class="pagination-wrap pagination-wrap--top">{{ $users->links() }}</div>
            @endif

            <div class="user-table-wrap">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Créé le</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @php $isSelf = auth()->id() === $user->id; @endphp
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <span class="user-avatar">
                                            @if($user->avatar)
                                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                                            @else
                                                {{ mb_substr($user->name, 0, 1) }}
                                            @endif
                                        </span>
                                        <span class="user-name">
                                            {{ $user->name }}
                                            @if($isSelf)<span class="user-you">(vous)</span>@endif
                                        </span>
                                    </div>
                                </td>
                                <td class="user-email">{{ $user->email }}</td>
                                <td>
                                    <span class="role-pill is-{{ $user->role }}">
                                        {{ $roles[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td class="row-date">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button"
                                            class="row-icon-btn"
                                            title="Modifier"
                                            data-edit-btn
                                            data-action="{{ route('users.update', $user) }}"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->role }}"
                                            data-avatar="{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21H3v-4L16.5 3.5Z"/></svg>
                                        </button>
                                        <button type="button"
                                            class="row-icon-btn row-icon-btn--danger"
                                            title="{{ $isSelf ? 'Vous ne pouvez pas supprimer votre propre compte' : 'Supprimer' }}"
                                            data-delete-btn
                                            data-action="{{ route('users.destroy', $user) }}"
                                            data-name="{{ $user->name }}"
                                            {{ $isSelf ? 'disabled' : '' }}>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 7"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="pagination-wrap">{{ $users->links() }}</div>
            @endif
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                <strong>Aucun utilisateur pour le moment</strong>
                <p>Créez le premier compte pour donner accès au tableau de bord.</p>
            </div>
        @endif
    </div>
</section>

{{-- ===================== Modale : formulaire de création ===================== --}}
<div class="modal-overlay" id="userModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>👤</span>
                    <span>Nouvel utilisateur</span>
                </div>
                <button type="button" class="modal-card__close" id="closeModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="{{ route('users.store') }}" method="POST" class="user-form" enctype="multipart/form-data" data-turbo="false" autocomplete="off">
                    @csrf
                    <input type="hidden" name="_form" value="create">

                    <div class="field">
                        <label for="createAvatar">Photo de profil</label>
                        <div class="avatar-field">
                            <span class="avatar-preview" id="createAvatarPreview">👤</span>
                            <div class="avatar-field__input">
                                <input type="file" id="createAvatar" name="avatar"
                                       accept="image/png,image/jpeg,image/webp"
                                       class="{{ $createError('avatar') ? 'has-error' : '' }}">
                                @if($createError('avatar'))
                                    <p class="field-error">{{ $createError('avatar') }}</p>
                                @else
                                    <p class="field-hint">JPG, PNG ou WebP · 2 Mo maximum · facultatif.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="createName">Nom complet <span class="req">*</span></label>
                        <input type="text" id="createName" name="name" value="{{ $isEditError ? '' : old('name') }}"
                               maxlength="255" placeholder="Ex. : Jean Rakoto"
                               class="{{ $createError('name') ? 'has-error' : '' }}" required>
                        @if($createError('name'))<p class="field-error">{{ $createError('name') }}</p>@endif
                    </div>

                    <div class="field">
                        <label for="createEmail">Adresse email <span class="req">*</span></label>
                        <input type="email" id="createEmail" name="email" value="{{ $isEditError ? '' : old('email') }}"
                               maxlength="255" placeholder="nom@exemple.com"
                               class="{{ $createError('email') ? 'has-error' : '' }}" required>
                        @if($createError('email'))<p class="field-error">{{ $createError('email') }}</p>@endif
                    </div>

                    <div class="field">
                        <label for="createRole">Rôle <span class="req">*</span></label>
                        <select id="createRole" name="role" class="{{ $createError('role') ? 'has-error' : '' }}" required>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ (! $isEditError ? old('role', 'user') : 'user') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if($createError('role'))
                            <p class="field-error">{{ $createError('role') }}</p>
                        @else
                            <p class="field-hint" id="createRoleHint"></p>
                        @endif
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="createPassword">Mot de passe <span class="req">*</span></label>
                            <input type="password" id="createPassword" name="password" minlength="8"
                                   autocomplete="new-password"
                                   class="{{ $createError('password') ? 'has-error' : '' }}" required>
                            @if($createError('password'))<p class="field-error">{{ $createError('password') }}</p>@endif
                        </div>
                        <div class="field">
                            <label for="createPasswordConfirm">Confirmation <span class="req">*</span></label>
                            <input type="password" id="createPasswordConfirm" name="password_confirmation" minlength="8"
                                   autocomplete="new-password" required>
                        </div>
                    </div>

                    <div style="margin-top:18px;">
                        <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;">
                            Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modale : formulaire de modification ===================== --}}
<div class="modal-overlay" id="userEditModalOverlay">
    <div class="modal-panel">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>✏️</span>
                    <span>Modifier l'utilisateur</span>
                </div>
                <button type="button" class="modal-card__close" id="closeEditModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <form action="" method="POST" class="user-form" id="userEditForm" enctype="multipart/form-data" data-turbo="false" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">
                    <input type="hidden" name="_edit_id" id="editUserId" value="{{ $isEditError ? old('_edit_id') : '' }}">

                    <div class="field">
                        <label for="editAvatar">Photo de profil</label>
                        <div class="avatar-field">
                            <span class="avatar-preview" id="editAvatarPreview">?</span>
                            <div class="avatar-field__input">
                                <input type="file" id="editAvatar" name="avatar"
                                       accept="image/png,image/jpeg,image/webp"
                                       class="{{ $editError('avatar') ? 'has-error' : '' }}">
                                @if($editError('avatar'))
                                    <p class="field-error">{{ $editError('avatar') }}</p>
                                @else
                                    <p class="field-hint">JPG, PNG ou WebP · 2 Mo maximum. Laissez vide pour conserver la photo.</p>
                                @endif
                                <label class="check-line" id="editRemoveAvatarWrap" style="display:none;">
                                    <input type="checkbox" name="remove_avatar" value="1" id="editRemoveAvatar">
                                    Supprimer la photo actuelle
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="editName">Nom complet <span class="req">*</span></label>
                        <input type="text" id="editName" name="name" maxlength="255"
                               class="{{ $editError('name') ? 'has-error' : '' }}" required>
                        @if($editError('name'))<p class="field-error">{{ $editError('name') }}</p>@endif
                    </div>

                    <div class="field">
                        <label for="editEmail">Adresse email <span class="req">*</span></label>
                        <input type="email" id="editEmail" name="email" maxlength="255"
                               class="{{ $editError('email') ? 'has-error' : '' }}" required>
                        @if($editError('email'))<p class="field-error">{{ $editError('email') }}</p>@endif
                    </div>

                    <div class="field">
                        <label for="editRole">Rôle <span class="req">*</span></label>
                        <select id="editRole" name="role" class="{{ $editError('role') ? 'has-error' : '' }}" required>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @if($editError('role'))<p class="field-error">{{ $editError('role') }}</p>@endif
                        <p class="field-hint" id="editRoleHint"></p>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="editPassword">Nouveau mot de passe</label>
                            <input type="password" id="editPassword" name="password" minlength="8"
                                   autocomplete="new-password"
                                   class="{{ $editError('password') ? 'has-error' : '' }}">
                            @if($editError('password'))
                                <p class="field-error">{{ $editError('password') }}</p>
                            @else
                                <p class="field-hint">Laissez vide pour le conserver.</p>
                            @endif
                        </div>
                        <div class="field">
                            <label for="editPasswordConfirm">Confirmation</label>
                            <input type="password" id="editPasswordConfirm" name="password_confirmation" minlength="8"
                                   autocomplete="new-password">
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
<div class="modal-overlay" id="userDeleteModalOverlay">
    <div class="modal-panel" style="max-width: 420px;">
        <div class="modal-card">
            <div class="modal-card__head">
                <div class="modal-card__title">
                    <span>🗑️</span>
                    <span>Supprimer l'utilisateur</span>
                </div>
                <button type="button" class="modal-card__close" id="closeDeleteModalBtn" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="modal-card__body">
                <p style="font-size:14px; line-height:1.6; color: var(--plomb); margin: 0 0 20px;">
                    Vous allez supprimer le compte <strong id="deleteUserName" style="color: var(--blanc);"></strong>.
                    Cette action est définitive : l'utilisateur ne pourra plus se connecter.
                </p>
                <form action="" method="POST" id="userDeleteForm" data-turbo="false" style="display:flex; gap:10px;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn" id="cancelDeleteBtn" style="flex:1; justify-content:center; border-color: var(--glass-bord);">
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
        const lockBody = () => document.body.classList.add('modal-lock');
        const unlockBody = () => document.body.classList.remove('modal-lock');

        // ---------- Modale de création ----------
        const overlay = document.getElementById('userModalOverlay');
        const openBtn = document.getElementById('openCreateModalBtn');
        const closeBtn = document.getElementById('closeModalBtn');
        if (!overlay || !openBtn) return;

        function openModal() { overlay.classList.add('is-open'); lockBody(); }
        function closeModal() { overlay.classList.remove('is-open'); unlockBody(); }

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });

        // ---------- Modale de modification ----------
        const editOverlay = document.getElementById('userEditModalOverlay');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const editForm = document.getElementById('userEditForm');
        const editId = document.getElementById('editUserId');
        const editName = document.getElementById('editName');
        const editEmail = document.getElementById('editEmail');
        const editRole = document.getElementById('editRole');
        const editPassword = document.getElementById('editPassword');
        const editPasswordConfirm = document.getElementById('editPasswordConfirm');

        function openEditModal() { editOverlay.classList.add('is-open'); lockBody(); }
        function closeEditModal() { editOverlay.classList.remove('is-open'); unlockBody(); }

        document.querySelectorAll('[data-edit-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                editForm.action = btn.dataset.action;
                editId.value = btn.dataset.id;
                editName.value = btn.dataset.name;
                editEmail.value = btn.dataset.email;
                editRole.value = btn.dataset.role;
                editRole.dispatchEvent(new Event('change'));
                editPassword.value = '';
                editPasswordConfirm.value = '';

                // Photo de profil
                editAvatarCurrent = btn.dataset.avatar || '';
                editAvatarInput.value = '';
                editRemoveAvatar.checked = false;
                editRemoveWrap.style.display = editAvatarCurrent ? '' : 'none';
                renderPreview(editAvatarPreview, editAvatarCurrent, (btn.dataset.name || '?').charAt(0));

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
        const deleteOverlay = document.getElementById('userDeleteModalOverlay');
        const closeDeleteBtn = document.getElementById('closeDeleteModalBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const deleteForm = document.getElementById('userDeleteForm');
        const deleteUserName = document.getElementById('deleteUserName');

        function openDeleteModal() { deleteOverlay.classList.add('is-open'); lockBody(); }
        function closeDeleteModal() { deleteOverlay.classList.remove('is-open'); unlockBody(); }

        document.querySelectorAll('[data-delete-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.disabled) return;
                deleteForm.action = btn.dataset.action;
                deleteUserName.textContent = btn.dataset.name;
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

        // ---------- Photo de profil : aperçu ----------
        function renderPreview(box, url, fallback) {
            box.innerHTML = '';
            if (url) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = '';
                box.appendChild(img);
            } else {
                box.textContent = fallback;
            }
        }

        const createAvatarInput = document.getElementById('createAvatar');
        const createAvatarPreview = document.getElementById('createAvatarPreview');
        createAvatarInput.addEventListener('change', function () {
            const file = createAvatarInput.files[0];
            renderPreview(createAvatarPreview, file ? URL.createObjectURL(file) : '', '👤');
        });

        let editAvatarCurrent = '';
        const editAvatarInput = document.getElementById('editAvatar');
        const editAvatarPreview = document.getElementById('editAvatarPreview');
        const editRemoveAvatar = document.getElementById('editRemoveAvatar');
        const editRemoveWrap = document.getElementById('editRemoveAvatarWrap');

        editAvatarInput.addEventListener('change', function () {
            const file = editAvatarInput.files[0];
            if (file) {
                editRemoveAvatar.checked = false;
                renderPreview(editAvatarPreview, URL.createObjectURL(file), '');
            } else {
                renderPreview(editAvatarPreview, editAvatarCurrent, (editName.value || '?').charAt(0));
            }
        });
        editRemoveAvatar.addEventListener('change', function () {
            if (editRemoveAvatar.checked) {
                editAvatarInput.value = '';
                renderPreview(editAvatarPreview, '', (editName.value || '?').charAt(0));
            } else {
                renderPreview(editAvatarPreview, editAvatarCurrent, (editName.value || '?').charAt(0));
            }
        });

        // ---------- Description du rôle sélectionné ----------
        const ROLE_DESCRIPTIONS = @json($roleDescriptions);
        function bindRoleHint(select, hint) {
            if (!select || !hint) return;
            const update = function () { hint.textContent = ROLE_DESCRIPTIONS[select.value] || ''; };
            select.addEventListener('change', update);
            update();
        }
        bindRoleHint(document.getElementById('createRole'), document.getElementById('createRoleHint'));
        bindRoleHint(editRole, document.getElementById('editRoleHint'));

        // Échap : ferme la modale actuellement ouverte
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (overlay.classList.contains('is-open')) closeModal();
            if (editOverlay.classList.contains('is-open')) closeEditModal();
            if (deleteOverlay.classList.contains('is-open')) closeDeleteModal();
        });

        // ---------- Réouverture automatique après erreur de validation ----------
        @if($errors->any())
            @if($isEditError)
                editForm.action = @json(route('users.update', ['user' => '__ID__'])).replace('__ID__', editId.value);
                editName.value = @json(old('name', ''));
                editEmail.value = @json(old('email', ''));
                editRole.value = @json(old('role', 'user'));
                editRole.dispatchEvent(new Event('change'));
                openEditModal();
            @else
                openModal();
            @endif
        @endif

        // ---------- Toast de notification (auto-disparition) ----------
        const pageToast = document.getElementById('pageToast');
        if (pageToast) {
            const pageToastClose = document.getElementById('pageToastClose');
            requestAnimationFrame(function () {
                pageToast.classList.add('is-visible');
            });

            function hideToast() { pageToast.classList.remove('is-visible'); }

            let toastTimer = setTimeout(hideToast, 5000);

            if (pageToastClose) {
                pageToastClose.addEventListener('click', function () {
                    clearTimeout(toastTimer);
                    hideToast();
                });
            }
        }
    });
</script>
@endpush