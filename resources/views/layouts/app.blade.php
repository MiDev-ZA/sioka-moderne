<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SIOKA — Serasera lombonan'ny Olompirenena ho Kalitaon'ny Antontambaovao. FM 106 Mhz.">
  <title>@yield('title', 'SIOKA - Za i\'Sioka rivo-baovao!')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght=0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&display=swap" rel="stylesheet">
  <script>
  (function () {
    try {
      var theme = localStorage.getItem('sioka-theme');
      if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    } catch (e) {}
  })();
</script>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/icones/favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/icones/favicon-96x96.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

  <!-- Apple -->
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icones/apple-touch-icon.png') }}">

  <!-- PWA -->
  <link rel="manifest" href="{{ asset('images/icones/site.webmanifest') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icones/web-app-manifest-192x192.png') }}">
  <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icones/web-app-manifest-512x512.png') }}">
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Pour analytics -->
  @include('partials.analytics') 
</head>
<body>

  @include('partials.ticker')
  @include('partials.header')
  @include('partials.search-modal')

  <main>
    @yield('content')
  </main>

  @include('partials.footer')
  @include('partials.radio-player')

  @include('partials.loading-overlay')

  @stack('scripts')

</body>
</html>