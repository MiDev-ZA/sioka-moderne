@extends('layouts.dashboard-layout')

@section('title', 'Mon profil - SIOKA')

@section('content')
@php
    $user = auth()->user();
    $roles = [
        'admin' => 'Administrateur', 'editor' => 'Éditeur', 'journalist' => 'Journaliste',
        'producer' => 'Producteur', 'integrator' => 'Intégrateur', 'user' => 'Utilisateur',
    ];
    $avatarUrl = $user->avatar ? asset('storage/' . ltrim($user->avatar, '/')) : null;
    $initials  = mb_strtoupper(collect(preg_split('/\s+/', trim($user->name)))
        ->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    $tab = (session('tab') === 'security' || $errors->hasBag('password')) ? 'security' : 'info';
@endphp

<style>
    .pf-head { display: flex; align-items: center; gap: 18px; padding: 24px; flex-wrap: wrap; border-bottom: 1px solid var(--glass-bord); }
    .pf-avatar { width: 96px; height: 96px; border-radius: 50%; border: 3px solid var(--glass-bord); background: var(--ink-4); color: var(--blanc);
        display: grid; place-items: center; font-size: 30px; font-weight: 700; overflow: hidden; flex: none; }
    .pf-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pf-id h2 { margin: 0; font-size: 22px; }
    .pf-id p { margin: 2px 0 0; color: var(--plomb); font-size: 14px; }
    .pf-badge { display: inline-block; margin-top: 8px; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; background: var(--bleu); color: #fff; }
    .pf-tabs { display: flex; gap: 6px; padding: 0 24px; border-bottom: 1px solid var(--glass-bord); }
    .pf-tabs button { background: none; border: 0; padding: 12px 14px; font: inherit; font-weight: 600; color: var(--plomb); cursor: pointer; border-bottom: 3px solid transparent; }
    .pf-tabs button.on { color: var(--blanc); border-bottom-color: var(--bleu); }
    .pf-pane { display: none; padding: 24px; max-width: 560px; }
    .pf-pane.on { display: block; }
    .pf-field { margin-bottom: 16px; }
    .pf-field label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
    .pf-field input[type=text], .pf-field input[type=email], .pf-field input[type=password] {
        width: 100%; padding: 10px 12px; border-radius: var(--r-sm); border: 1px solid var(--glass-bord);
        background: var(--ink-3); color: var(--blanc); font: inherit; }
    .pf-field input:focus-visible, .pf-tabs button:focus-visible { outline: 2px solid var(--bleu); outline-offset: 2px; }
    .pf-err { color: #e5484d; font-size: 13px; margin-top: 4px; }
    .pf-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .pf-btn { padding: 10px 18px; border: 0; border-radius: var(--r-sm); font: inherit; font-weight: 600; cursor: pointer; background: var(--bleu); color: #fff; }
    .pf-btn.ghost { background: transparent; color: var(--blanc); border: 1px solid var(--glass-bord); }
    .pf-photo { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }

    /* ==================== Toast de notification (identique aux émissions) ==================== */
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
    <div class="toast toast--success" id="pageToast" role="status">
        <span class="toast__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
        </span>
        <span>{{ session('success') }}</span>
        <button type="button" class="toast__close" id="pageToastClose" aria-label="Fermer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
@endif

<div class="panel" style="padding:0;overflow:hidden">
    <div class="pf-head">
        <div class="pf-avatar" id="pfAvatarMain">
            @if ($user->avatar)
                <img src="{{ $avatarUrl }}" alt="" onerror="this.replaceWith({{ json_encode($initials) }})">
            @else
                {{ $initials }}
            @endif
        </div>
        <div class="pf-id">
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }} · membre depuis {{ $user->created_at?->locale('fr')->translatedFormat('F Y') }}</p>
            <span class="pf-badge">{{ $roles[$user->role] ?? ucfirst($user->role) }}</span>
        </div>
    </div>

    <div class="pf-tabs" role="tablist">
        <button type="button" role="tab" data-tab="info" class="{{ $tab === 'info' ? 'on' : '' }}">Informations</button>
        <button type="button" role="tab" data-tab="security" class="{{ $tab === 'security' ? 'on' : '' }}">Sécurité</button>
    </div>

    {{-- ── Informations personnelles ── --}}
    <div class="pf-pane {{ $tab === 'info' ? 'on' : '' }}" id="pane-info">
        <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" data-turbo="false">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_avatar" id="removeAvatar" value="0">

            <div class="pf-photo">
                <div class="pf-avatar" id="pfAvatarPreview" style="width:72px;height:72px;font-size:22px">
                    @if ($user->avatar)
                        <img src="{{ $avatarUrl }}" alt="" onerror="this.replaceWith({{ json_encode($initials) }})">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="pf-actions">
                    <label class="pf-btn ghost" for="avatarInput" style="cursor:pointer">Changer la photo</label>
                    <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>
                    @if ($user->avatar)
                        <button type="button" class="pf-btn ghost" id="btnRemoveAvatar">Supprimer la photo</button>
                    @endif
                </div>
            </div>
            @error('avatar') <div class="pf-err" style="margin:-12px 0 14px">{{ $message }}</div> @enderror

            <div class="pf-field">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
                @error('name') <div class="pf-err">{{ $message }}</div> @enderror
            </div>

            <div class="pf-field">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
                @error('email') <div class="pf-err">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="pf-btn">Enregistrer les modifications</button>
        </form>
    </div>

    {{-- ── Sécurité ── --}}
    <div class="pf-pane {{ $tab === 'security' ? 'on' : '' }}" id="pane-security">
        <form method="POST" action="{{ route('profil.password') }}" data-turbo="false">
            @csrf
            @method('PUT')

            <div class="pf-field">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                @if ($errors->password->has('current_password')) <div class="pf-err">{{ $errors->password->first('current_password') }}</div> @endif
            </div>

            <div class="pf-field">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" autocomplete="new-password" minlength="8" required>
                @if ($errors->password->has('password')) <div class="pf-err">{{ $errors->password->first('password') }}</div> @endif
            </div>

            <div class="pf-field">
                <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>

            <button type="submit" class="pf-btn">Changer le mot de passe</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        // Onglets
        const tabs = document.querySelectorAll('.pf-tabs button');
        tabs.forEach(btn => btn.addEventListener('click', () => {
            tabs.forEach(b => b.classList.toggle('on', b === btn));
            document.querySelectorAll('.pf-pane').forEach(p => p.classList.remove('on'));
            document.getElementById('pane-' + btn.dataset.tab).classList.add('on');
        }));

        // Aperçu immédiat de la nouvelle photo
        const input = document.getElementById('avatarInput');
        const preview = document.getElementById('pfAvatarPreview');
        const removeFlag = document.getElementById('removeAvatar');
        const removeBtn = document.getElementById('btnRemoveAvatar');

        input?.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('La photo ne doit pas dépasser 2 Mo.');
                input.value = '';
                return;
            }
            removeFlag.value = '0';
            preview.innerHTML = '<img alt="" src="' + URL.createObjectURL(file) + '">';
        });

        removeBtn?.addEventListener('click', () => {
            removeFlag.value = '1';
            input.value = '';
            preview.textContent = @json($initials);
            removeBtn.hidden = true;
        });

        // Toast de notification (auto-disparition)
        const pageToast = document.getElementById('pageToast');
        if (pageToast) {
            const hideToast = () => pageToast.classList.remove('is-visible');
            const toastTimer = setTimeout(hideToast, 5000);
            requestAnimationFrame(() => pageToast.classList.add('is-visible'));
            document.getElementById('pageToastClose')?.addEventListener('click', () => {
                clearTimeout(toastTimer);
                hideToast();
            });
        }
    })();
</script>
@endpush