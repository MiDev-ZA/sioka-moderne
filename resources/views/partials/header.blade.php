@php
    // Catégories du menu Actualités (affichées en desktop et en mobile)
    $navCategories = [
        'a-la-une'      => 'À la Une',
        'politique'     => 'Politique',
        'societe'       => 'Société',
        'economie'      => 'Économie',
        'sport'         => 'Sport',
        'culture'       => 'Culture',
        'international' => 'International',
    ];

    // Les ancres pointent vers l'accueil : elles fonctionnent depuis n'importe quelle page
    $home = route('home');

    // Rubriques actives selon la route courante
    $isActu  = request()->routeIs('news.index', 'categories.show', 'articles.show');
    $isWebtv = request()->routeIs('webtv.index', 'webtv.show');
@endphp

<header class="header">
    <div class="header-row">
        <button type="button" class="hbg" id="hbg" aria-label="Ouvrir le menu"
            aria-expanded="false" aria-controls="mobileNav">
            <span class="hbg-bar"></span>
            <span class="hbg-bar"></span>
            <span class="hbg-bar"></span>
        </button>

        <a href="{{ $home }}" class="logo" aria-label="SIOKA Web TV &amp; Radio">
            <img src="{{ asset('images/SIOKA-LogoOfficiel.png') }}" alt="SIOKA" class="logo-image">
        </a>

        <nav class="nav-main" aria-label="Navigation principale">
            <a href="{{ $home }}" class="{{ request()->routeIs('home') ? 'active-magenta' : '' }}">Accueil</a>

            <div class="nav-item" id="navActualites">
                <button type="button" class="nav-link nav-trigger {{ $isActu ? 'active-magenta' : '' }}" id="actuTrigger"
                    aria-haspopup="true" aria-expanded="false">
                    Actualités
                    <svg class="nav-caret" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>
                <div class="nav-dropdown" id="actuDropdown" role="menu">
                    <div class="nav-dropdown-inner">
                        @foreach ($navCategories as $slug => $label)
                            <a href="{{ route('categories.show', $slug) }}" role="menuitem"
                                class="{{ request()->is('categories/' . $slug) ? 'active-magenta' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ $home }}#webtv" class="nav-webtv {{ $isWebtv ? 'active-magenta' : '' }}"><span class="webtv-dot" aria-hidden="true"></span>WebTV</a>
            <a href="{{ route('emissions.public') }}"
                class="{{ request()->routeIs('emissions.public') ? 'active-magenta' : '' }}">Émissions</a>
            <a href="{{ $home }}#podcasts">Podcasts</a>
            <a href="{{ route('equipe.public') }}"
                class="{{ request()->routeIs('equipe.public') ? 'active-magenta' : '' }}">L'Équipe</a>
            <a href="{{ $home }}#contact">Contact</a>
            <a href="{{ $home }}#partenaires">Partenaires</a>
        </nav>

        <div class="header-actions">
            <button type="button" class="btn-live-radio" aria-label="Écouter la radio en direct" onclick="togglePlay()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="7" width="18" height="13" rx="2" />
                    <line x1="7" y1="3" x2="17" y2="7" />
                    <circle cx="8" cy="13" r="2" />
                    <line x1="13" y1="12" x2="18" y2="12" />
                    <line x1="13" y1="15" x2="18" y2="15" />
                </svg>
                Le Direct
            </button>

            <button type="button" class="h-btn theme-toggle" id="themeToggle" aria-label="Changer de thème"
                title="Mode sombre / clair">
                <svg class="icon-moon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                </svg>
                <svg class="icon-sun" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <circle cx="12" cy="12" r="4" />
                    <path
                        d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                </svg>
            </button>

            @guest
                <a href="{{ route('login') }}" class="btn-login" target="_blank" rel="noopener noreferrer"
                    aria-label="Se connecter">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <polyline points="10 17 15 12 10 7" />
                        <line x1="15" y1="12" x2="3" y2="12" />
                    </svg>
                    <span class="btn-login-label"></span>
                </a>
            @endguest

            @auth
                <div class="user-menu" id="userMenu">
                    <button type="button" class="user-avatar-btn" id="userMenuBtn" aria-haspopup="true"
                        aria-expanded="false" aria-label="Menu du compte">
                        @if (auth()->user()->avatar ?? false)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
                        @else
                            <span class="avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                    </button>

                    <div class="user-menu-card" id="userMenuCard" role="menu">
                        <div class="user-menu-head">
                            <div class="um-name">{{ auth()->user()->name }}</div>
                            <div class="um-email">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="{{ route('profil') }}" role="menuitem">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="7" r="4" />
                                <path d="M5.5 21a6.5 6.5 0 0 1 13 0" />
                            </svg>
                            Mon profil
                        </a>
                        <a href="{{ route('dashboard') }}" role="menuitem">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="9" />
                                <rect x="14" y="3" width="7" height="5" />
                                <rect x="14" y="12" width="7" height="9" />
                                <rect x="3" y="16" width="7" height="5" />
                            </svg>
                            Tableau de bord
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="um-logout" role="menuitem">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            <button type="button" class="h-btn" id="searchBtn" aria-label="Rechercher" title="Rechercher">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
            </button>
        </div>
    </div>

    <nav class="mobile-nav" id="mobileNav" aria-label="Navigation mobile">
        <a href="#" role="button" style="color: var(--magenta);"
            onclick="event.preventDefault(); togglePlay();">Écouter FM 106 en direct</a>

        <a href="{{ $home }}" class="{{ request()->routeIs('home') ? 'active-magenta' : '' }}">Accueil</a>

        <div class="mnav-item">
            <button type="button" class="mnav-trigger {{ $isActu ? 'active-magenta' : '' }}" id="actuTriggerMobile" aria-expanded="false"
                aria-controls="actuPanelMobile">
                Actualités
                <svg class="nav-caret" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>

            <div class="mnav-panel" id="actuPanelMobile">
                <div class="mnav-panel-inner">
                    @foreach ($navCategories as $slug => $label)
                        <a href="{{ route('categories.show', $slug) }}"
                           class="{{ request()->is('categories/' . $slug) ? 'active-magenta' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <a href="{{ $home }}#webtv" class="nav-webtv {{ $isWebtv ? 'active-magenta' : '' }}"><span class="webtv-dot" aria-hidden="true"></span>WebTV</a>
        <a href="{{ route('emissions.public') }}"
            class="{{ request()->routeIs('emissions.public') ? 'active-magenta' : '' }}">Émissions</a>
        <a href="{{ $home }}#podcasts">Podcasts</a>
        <a href="{{ route('equipe.public') }}"
            class="{{ request()->routeIs('equipe.public') ? 'active-magenta' : '' }}">L'Équipe</a>
        <a href="{{ $home }}#contact">Contact</a>
        <a href="{{ $home }}#partenaires">Partenaires</a>

        <div class="mobile-nav-utility">
            @guest
                <a href="{{ route('login') }}" class="btn-login" style="flex:1; justify-content:center;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <polyline points="10 17 15 12 10 7" />
                        <line x1="15" y1="12" x2="3" y2="12" />
                    </svg>
                    <span class="btn-login-label">Connexion</span>
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn-login" style="flex:1; justify-content:center;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="9" />
                        <rect x="14" y="3" width="7" height="5" />
                        <rect x="14" y="12" width="7" height="9" />
                        <rect x="3" y="16" width="7" height="5" />
                    </svg>
                    <span class="btn-login-label">Tableau de bord</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" style="display:contents;">
                    @csrf
                    <button type="submit" class="btn-login" style="flex:1; justify-content:center;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        <span class="btn-login-label">Déconnexion</span>
                    </button>
                </form>
            @endguest

            <button type="button" class="h-btn theme-toggle" id="themeToggleMobile" aria-label="Changer de thème"
                title="Mode sombre / clair">
                <svg class="icon-moon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                </svg>
                <svg class="icon-sun" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <circle cx="12" cy="12" r="4" />
                    <path
                        d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                </svg>
            </button>
        </div>
    </nav>
</header>

<script>
    (function () {
        // Évite de lier les écouteurs plusieurs fois (navigation Turbo, header inclus deux fois)
        if (window.__siokaHeaderReady) return;
        window.__siokaHeaderReady = true;

        const root = document.documentElement;
        const KEY  = 'sioka-theme';
        const $    = function (id) { return document.getElementById(id); };

        // ── Thème clair / sombre ──────────────────────────────────
        function applyTheme(theme) {
            if (theme === 'light') {
                root.setAttribute('data-theme', 'light');
            } else {
                root.removeAttribute('data-theme');
            }
        }

        function currentTheme() {
            return root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        }

        function toggleTheme() {
            const next = currentTheme() === 'light' ? 'dark' : 'light';
            applyTheme(next);
            try { localStorage.setItem(KEY, next); } catch (e) {}
        }

        // Relit le thème enregistré au chargement
        try {
            const saved = localStorage.getItem(KEY);
            if (saved === 'light' || saved === 'dark') applyTheme(saved);
        } catch (e) {}

        // ── Ouverture / fermeture des menus ───────────────────────
        function setOpen(panel, trigger, open) {
            if (!panel || !trigger) return;
            panel.classList.toggle('open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function isOpen(el) { return !!el && el.classList.contains('open'); }

        function closeUserMenu() { setOpen($('userMenuCard'), $('userMenuBtn'), false); }
        function closeActu()     { setOpen($('actuDropdown'), $('actuTrigger'), false); }

        // ── Clics (écouteur unique, délégué : fonctionne même si le DOM est remplacé) ──
        document.addEventListener('click', function (e) {
            const t = e.target;

            if (t.closest('#themeToggle, #themeToggleMobile')) {
                toggleTheme();
                return;
            }

            if (t.closest('#userMenuBtn')) {
                const open = !isOpen($('userMenuCard'));
                closeActu();
                setOpen($('userMenuCard'), $('userMenuBtn'), open);
                return;
            }

            if (t.closest('#actuTrigger')) {
                const open = !isOpen($('actuDropdown'));
                closeUserMenu();
                setOpen($('actuDropdown'), $('actuTrigger'), open);
                return;
            }

            if (t.closest('#actuTriggerMobile')) {
                setOpen($('actuPanelMobile'), $('actuTriggerMobile'), !isOpen($('actuPanelMobile')));
                return;
            }

            // Clic ailleurs (ou sur un lien du menu) : referme les menus déroulants
            if (!t.closest('#navActualites') || t.closest('#actuDropdown a')) closeActu();
            if (!t.closest('#userMenu')) closeUserMenu();
        });

        // ── Touche Échap ──────────────────────────────────────────
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;

            if (isOpen($('actuDropdown'))) {
                closeActu();
                if ($('actuTrigger')) $('actuTrigger').focus();
            }
            if (isOpen($('userMenuCard'))) {
                closeUserMenu();
                if ($('userMenuBtn')) $('userMenuBtn').focus();
            }
        });

        // ── Liens d'ancre (#webtv, #podcasts, #contact, #partenaires) ──
        // Sur l'accueil, le lien actif suit la section visible ; sans section visible, c'est « Accueil ».
        let spyItems = [];
        let spyHome = [];
        let spyTick = false;

        function buildSpy() {
            spyItems = [];
            spyHome = [];
            document.querySelectorAll('.nav-main a, .mobile-nav a').forEach(function (a) {
                const raw = a.getAttribute('href');
                if (!raw || raw === '#' || a.host !== location.host || a.pathname !== location.pathname) return;
                if (a.hash.length < 2) { spyHome.push(a); return; }
                let target = null;
                try { target = document.getElementById(decodeURIComponent(a.hash.slice(1))); } catch (e) {}
                if (target) spyItems.push({ link: a, target: target });
            });
            updateSpy();
        }

        function updateSpy() {
            if (!spyItems.length) return; // pas sur la page qui porte ces sections

            const header = document.querySelector('.header');
            const line = (header ? header.offsetHeight : 0) + 120;
            let current = null;

            spyItems.forEach(function (it) {
                const r = it.target.getBoundingClientRect();
                if (r.top <= line && r.bottom > line) current = it.target;
            });

            // Bas de page : la dernière section reste active même si elle est courte
            const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;
            if (atBottom) current = spyItems[spyItems.length - 1].target;

            spyItems.forEach(function (it) { it.link.classList.toggle('active-magenta', it.target === current); });
            spyHome.forEach(function (a) { a.classList.toggle('active-magenta', current === null); });
        }

        function scheduleSpy() {
            if (spyTick) return;
            spyTick = true;
            requestAnimationFrame(function () { spyTick = false; updateSpy(); });
        }

        window.addEventListener('scroll', scheduleSpy, { passive: true });
        window.addEventListener('resize', scheduleSpy);
        window.addEventListener('hashchange', scheduleSpy);
        document.addEventListener('DOMContentLoaded', buildSpy);
        document.addEventListener('turbo:load', buildSpy);
    })();
</script>