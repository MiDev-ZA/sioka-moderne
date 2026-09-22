<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - SIOKA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap"
        rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icones/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/icones/favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icones/apple-touch-icon.png') }}">

    <!-- PWA -->
    <link rel="manifest" href="{{ asset('images/icones/site.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192"
        href="{{ asset('images/icones/web-app-manifest-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512"
        href="{{ asset('images/icones/web-app-manifest-512x512.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<script>
    (function() {
        const theme = localStorage.getItem('sioka-theme');
        if (theme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            document.addEventListener('turbo:load', () => {
                const icon = document.querySelector('#themeToggle svg path');
                if (icon) {
                    icon.setAttribute('d',
                        'M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M17.66 6.34l1.41-1.41M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10z'
                        );
                }
            }, {
                once: true
            });
        }
    })();
</script>

<body class="dash-body">

    <div class="ambient">
        <div class="orb orb-a"></div>
        <div class="orb orb-b"></div>
    </div>

    @include('dashboard.partials.headbar')

    <div class="side-overlay" id="sideOverlay"></div>

    <div class="shell">

        @include('dashboard.partials.sidebar')

        <main class="main">
            @yield('content')
        </main>
    </div>

    @include('partials.loading-overlay')

    <script>
        (function() {
            const themeBtn = document.getElementById('themeToggle');
            if (themeBtn && !themeBtn.dataset.bound) {
                themeBtn.dataset.bound = 'true';
                themeBtn.addEventListener('click', () => {
                    const root = document.documentElement;
                    const icon = themeBtn.querySelector('svg path');
                    const isLight = root.getAttribute('data-theme') === 'light';

                    const animationClass = isLight ? 'animate-left' : 'animate-right';
                    themeBtn.classList.add(animationClass);

                    setTimeout(() => {
                        if (isLight) {
                            root.removeAttribute('data-theme');
                            localStorage.setItem('sioka-theme', 'dark');
                            icon.setAttribute('d', 'M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z');
                        } else {
                            root.setAttribute('data-theme', 'light');
                            localStorage.setItem('sioka-theme', 'light');
                            icon.setAttribute('d',
                                'M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M17.66 6.34l1.41-1.41M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10z'
                                );
                        }
                    }, 250);

                    setTimeout(() => {
                        themeBtn.classList.remove(animationClass);
                    }, 500);
                });
            }

            // Menu tiroir (mobile-first) : ouverture, overlay, fermeture au clic sur un lien
            const navToggle = document.getElementById('navToggle');
            const sideOverlay = document.getElementById('sideOverlay');

            if (navToggle && !navToggle.dataset.bound) {
                navToggle.dataset.bound = 'true';

                function openNav() {
                    document.body.classList.add('nav-open');
                    navToggle.setAttribute('aria-expanded', 'true');
                }

                function closeNav() {
                    document.body.classList.remove('nav-open');
                    navToggle.setAttribute('aria-expanded', 'false');
                }

                navToggle.addEventListener('click', () => {
                    document.body.classList.contains('nav-open') ? closeNav() : openNav();
                });
                if (sideOverlay) sideOverlay.addEventListener('click', closeNav);
                document.querySelectorAll('#dashSide .nav a').forEach(link => link.addEventListener('click', closeNav));
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') closeNav();
                });

                const desktopQuery = window.matchMedia('(min-width: 861px)');
                desktopQuery.addEventListener('change', (e) => {
                    if (e.matches) closeNav();
                });
            }
        })();
    </script>

    @stack('scripts')

</body>

</html>
