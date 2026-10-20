@if (config('services.google_analytics.id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', '{{ config('services.google_analytics.id') }}', { send_page_view: false });

        // Turbo : un page_view à chaque affichage de page, y compris le premier chargement
        document.addEventListener('turbo:load', function () {
            gtag('event', 'page_view', {
                page_title: document.title,
                page_location: window.location.href,
            });
        });
    </script>
@endif