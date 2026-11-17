@if (config('services.google_analytics.id'))
    @php
        // DebugView : actif par défaut en local (APP_ENV=local), inactif en production ; GA_DEBUG=true/false force le choix
        $gaDebug = config('services.google_analytics.debug') ?? app()->environment('local');
    @endphp
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', '{{ config('services.google_analytics.id') }}', {
            send_page_view: false,
            @if ($gaDebug)
            debug_mode: true, // visible dans GA → Admin → DebugView
            @endif
        });

        // Turbo : un page_view à chaque affichage de page, y compris le premier chargement
        document.addEventListener('turbo:load', function () {
            gtag('event', 'page_view', {
                page_title: document.title,
                page_location: window.location.href,
            });
        });

        // Suivi des clics : fonction globale réutilisable depuis n'importe quel script
        window.siokaTrack = function (name, params) {
            if (typeof gtag === 'function') { gtag('event', name, params || {}); }
        };

        // Écoute déléguée (une seule fois, compatible Turbo) : tout élément portant data-track est compté
        // Ex. : <a href="..." data-track="article_click" data-track-title="Titre de l'article">
        // data-track-start-only : bouton lecture/pause, on ne compte que le démarrage (l'état est lu avant le clic)
        if (!window.__siokaTrackBound) {
            window.__siokaTrackBound = true;
            document.addEventListener('click', function (e) {
                var el = e.target.closest ? e.target.closest('[data-track]') : null;
                if (!el) { return; }
                if (el.hasAttribute('data-track-start-only') &&
                    (el.matches('[aria-pressed="true"]') || el.querySelector('[aria-pressed="true"]'))) { return; }
                window.siokaTrack(el.getAttribute('data-track'), {
                    item_title: el.getAttribute('data-track-title') || '',
                    item_url: el.href || el.getAttribute('data-track-url') || ''
                });
            }, true);
        }
    </script>
@endif