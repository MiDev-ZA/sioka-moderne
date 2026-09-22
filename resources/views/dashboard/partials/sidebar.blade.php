<aside class="side" id="dashSide">
    <div class="side-card">
        <div class="side-user">
            <div class="side-avatar">
                <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
            <div>
                <h3>{{ auth()->user()->name }}</h3>
                <p>{{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </div>

        <nav class="nav">
            <div class="nav-label">Général</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                data-page-loading="Chargement du tableau de bord…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="9" />
                    <rect x="14" y="3" width="7" height="5" />
                    <rect x="14" y="12" width="7" height="9" />
                    <rect x="3" y="16" width="7" height="5" />
                </svg>
                Vue d'ensemble
            </a>
            <a href="{{ route('actualites') }}" class="{{ request()->routeIs('actualites') ? 'active' : '' }}"
                data-page-loading="Chargement des actualités…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                    <path d="M14 4v5h5" />
                </svg>
                Actualités
            </a>
            <a href="{{ route('flash-infos') }}" class="{{ request()->routeIs('flash-infos') ? 'active' : '' }}"
                data-page-loading="Chargement des flash infos…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z" />
                </svg>
                Flash infos
            </a>
            <a href="{{ route('fil-infos') }}" class="{{ request()->routeIs('fil-infos') ? 'active' : '' }}"
                data-page-loading="Chargement du fil d'infos…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
                Fil d'infos en continu
            </a>

            <a href="{{ route('webtv') }}" class="{{ request()->routeIs('webtv') ? 'active' : '' }}" data-turbo="false"
                data-page-loading="Chargement de la WebTV…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="m10 8 6 4-6 4V8Z" />
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                </svg>
                WebTV
            </a>

            <a href="{{ route('emissions') }}" class="{{ request()->routeIs('emissions') ? 'active' : '' }}"
                data-page-loading="Chargement des émissions…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="2" />
                    <path
                        d="M16.24 7.76a6 6 0 0 1 0 8.49M7.76 16.24a6 6 0 0 1 0-8.49M19.07 4.93a10 10 0 0 1 0 14.14M4.93 19.07a10 10 0 0 1 0-14.14" />
                </svg>
                Émissions
            </a>
            <a href="{{ route('podcasts') }}" class="{{ request()->routeIs('podcasts') ? 'active' : '' }}"
                data-page-loading="Chargement des podcasts…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4l3 2" />
                </svg>
                Podcasts
            </a>
            <div class="nav-sep"></div>
            <div class="nav-label">Communauté</div>
            <a href="{{ route('abonnees-newsletter') }}"
                class="{{ request()->routeIs('abonnees-newsletter') ? 'active' : '' }}"
                data-page-loading="Chargement des abonnés…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="m22 7-10 5L2 7" />
                </svg>
                Abonnés newsletter
            </a>
            <a href="{{ route('lequipe') }}" class="{{ request()->routeIs('lequipe') ? 'active' : '' }}"
                data-page-loading="Chargement de l'équipe…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                L'équipe
            </a>
            <div class="nav-sep"></div>
            <div class="nav-label">Compte</div>
            <a href="{{ route('profil') }}" class="{{ request()->routeIs('profil') ? 'active' : '' }}"
                data-page-loading="Chargement du profil…">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="7" r="4" />
                    <path d="M5.5 21a6.5 6.5 0 0 1 13 0" />
                </svg>
                Mon profil
            </a>

            <form action="{{ route('logout') }}" method="POST" id="logoutForm" data-turbo="false"
                data-auth-loading="Déconnexion en cours…">
                @csrf
            </form>

            <a href="#" onclick="document.getElementById('logoutForm').requestSubmit(); return false;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Déconnexion
            </a>

        </nav>
    </div>
</aside>