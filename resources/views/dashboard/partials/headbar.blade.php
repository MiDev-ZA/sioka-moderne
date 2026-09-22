<header class="topbar">
    <div class="topbar-row">
        <button class="icon-btn nav-toggle" id="navToggle" aria-label="Ouvrir le menu" aria-controls="dashSide" aria-expanded="false">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>
        <a href="#" class="logo" aria-label="SIOKA WebRadio">
            <img src="{{ asset('images/SIOKA-LogoOfficiel.png') }}" alt="SIOKA" class="logo-image">
        </a>
        <div class="search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input type="text" placeholder="Rechercher un contenu…">
        </div>
        <div class="topbar-spacer"></div>
        <div class="topbar-actions">
            <button class="icon-btn" id="themeToggle" title="Changer de thème">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                </svg>
            </button>
            <button class="icon-btn" title="Notifications">
                <span class="ping"></span>
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
            </button>
            <div class="avatar-chip">
                <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
        </div>
    </div>
</header>