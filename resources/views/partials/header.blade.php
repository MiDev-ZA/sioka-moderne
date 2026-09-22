<header class="header">
    <div class="header-row">
        <button class="hbg" id="hbg" aria-label="Ouvrir le menu" aria-expanded="false">
            <span class="hbg-bar"></span>
            <span class="hbg-bar"></span>
            <span class="hbg-bar"></span>
        </button>

        <a href="/" class="logo" aria-label="SIOKA Web TV & Radio">
            <img src="{{ asset('images/SIOKA-LogoOfficiel.png') }}" alt="SIOKA" class="logo-image">
        </a>

        <nav class="nav-main" aria-label="Navigation principale">
            <a href="/" class="active-magenta">Accueil</a>

            <div class="nav-item" id="navActualites">
                <button class="nav-link nav-trigger" id="actuTrigger" aria-haspopup="true" aria-expanded="false">
                    Actualités
                    <svg class="nav-caret" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>
                <div class="nav-dropdown" id="actuDropdown" role="menu">
                    <div class="nav-dropdown-inner">
                        <a href="/categories/a-la-une" role="menuitem">À la Une</a>
                        <a href="/categories/politique" role="menuitem">Politique</a>
                        <a href="/categories/societe" role="menuitem">Société</a>
                        <a href="/categories/economie" role="menuitem">Économie</a>
                        <a href="/categories/sport" role="menuitem">Sport</a>
                        <a href="/categories/culture" role="menuitem">Culture</a>
                        <a href="/categories/international" role="menuitem">International</a>
                    </div>
                </div>
            </div>

            <a href="#emissions">Émissions</a>
            <a href="#podcasts">Podcasts</a>
            <a href="#equipe">L'Équipe</a>
            <a href="#contact">Contact</a>
            <a href="#partners">Partenaires</a>
        </nav>

        <div class="header-actions">
            <button class="btn-live-radio" aria-label="Écouter la radio en direct" onclick="togglePlay()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="7" width="18" height="13" rx="2" />
                    <line x1="7" y1="3" x2="17" y2="7" />
                    <circle cx="8" cy="13" r="2" />
                    <line x1="13" y1="12" x2="18" y2="12" />
                    <line x1="13" y1="15" x2="18" y2="15" />
                </svg>
                LE Direct
            </button>

            <button class="h-btn theme-toggle" id="themeToggle" aria-label="Changer de thème"
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
                    <button class="user-avatar-btn" id="userMenuBtn" aria-haspopup="true" aria-expanded="false"
                        aria-label="Menu du compte">
                        @if (auth()->user()->avatar ?? false)
                            <img src="{{ auth()->user()->avatar }}" alt="">
                        @else
                            <span class="avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                    </button>

                    <div class="user-menu-card" id="userMenuCard" role="menu">
                        <div class="user-menu-head">
                            <div class="um-name">{{ auth()->user()->name }}</div>
                            <div class="um-email">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="#" role="menuitem">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="7" r="4" />
                                <path d="M5.5 21a6.5 6.5 0 0 1 13 0" />
                            </svg>
                            Mon profil
                        </a>
                        <a href="{{ route('dashboard') }}" role="menuitem">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

            <button class="h-btn" id="searchBtn" aria-label="Rechercher" title="Rechercher">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
            </button>
        </div>
    </div>

    <nav class="mobile-nav" id="mobileNav" aria-label="Navigation mobile">
        <a href="#" style="color: var(--magenta);">Ecouter FM 106 en Direct </a>
        <a href="#">Accueil</a>

        <div class="mnav-item">
            <button class="mnav-trigger" id="actuTriggerMobile" aria-expanded="false"
                aria-controls="actuPanelMobile">
                Actualités
                <svg class="nav-caret" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>

            <div class="mnav-panel" id="actuPanelMobile">
                <div class="mnav-panel-inner">
                    <a href="/categories/a-la-une">À la Une</a>
                    <a href="/categories/politique">Politique</a>
                    <a href="/categories/societe">Société</a>
                    <a href="/categories/economie">Économie</a>
                    <a href="/categories/sport">Sport</a>
                    <a href="/categories/culture">Culture</a>
                    <a href="/categories/international">International</a>
                </div>
            </div>
        </div>

        <a href="#emissions">Émissions</a>
        <a href="#podcasts">Podcasts</a>
        <a href="#contact">Contact</a>

        <div class="mobile-nav-utility">
            @guest
                <button class="btn-login" onclick="window.location.href=''">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <polyline points="10 17 15 12 10 7" />
                        <line x1="15" y1="12" x2="3" y2="12" />
                    </svg>
                    <span class="btn-login-label">Connexion</span>
                </button>
            @else
                <button class="btn-login" onclick="window.location.href='{{ route('dashboard') }}'"
                    style="flex:1; justify-content:center;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="9" />
                        <rect x="14" y="3" width="7" height="5" />
                        <rect x="14" y="12" width="7" height="9" />
                        <rect x="3" y="16" width="7" height="5" />
                    </svg>
                    <span class="btn-login-label">Tableau de bord</span>
                </button>
            @endguest

            <button class="h-btn theme-toggle" id="themeToggleMobile" aria-label="Changer de thème"
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
    (function() {
        // ── Theme toggle ──────────────────────────────────────────
        const root = document.documentElement;
        const KEY = 'sioka-theme';

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
            try {
                localStorage.setItem(KEY, next);
            } catch (e) {}
        }

        document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
        document.getElementById('themeToggleMobile')?.addEventListener('click', toggleTheme);

        // ── User dropdown ─────────────────────────────────────────
        const menuBtn = document.getElementById('userMenuBtn');
        const menuCard = document.getElementById('userMenuCard');

        if (menuBtn && menuCard) {
            menuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const open = menuCard.classList.toggle('open');
                menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            document.addEventListener('click', (e) => {
                if (!menuCard.contains(e.target) && !menuBtn.contains(e.target)) {
                    menuCard.classList.remove('open');
                    menuBtn.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    menuCard.classList.remove('open');
                    menuBtn.setAttribute('aria-expanded', 'false');
                    menuBtn.focus();
                }
            });
        }

        // ── Dropdown Actualités (desktop) ──────────────────────────
        const actuTrigger = document.getElementById('actuTrigger');
        const actuDropdown = document.getElementById('actuDropdown');
        const navActu = document.getElementById('navActualites');

        if (actuTrigger && actuDropdown && navActu) {
            actuTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const open = actuDropdown.classList.toggle('open');
                actuTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            document.addEventListener('click', (e) => {
                if (!navActu.contains(e.target)) {
                    actuDropdown.classList.remove('open');
                    actuTrigger.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && actuDropdown.classList.contains('open')) {
                    actuDropdown.classList.remove('open');
                    actuTrigger.setAttribute('aria-expanded', 'false');
                    actuTrigger.focus();
                }
            });
        }

        // ── Accordéon Actualités (mobile) ──────────────────────────
        const actuTriggerMobile = document.getElementById('actuTriggerMobile');
        const actuPanelMobile = document.getElementById('actuPanelMobile');

        if (actuTriggerMobile && actuPanelMobile) {
            actuTriggerMobile.addEventListener('click', () => {
                const open = actuPanelMobile.classList.toggle('open');
                actuTriggerMobile.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }
    })();
</script>
