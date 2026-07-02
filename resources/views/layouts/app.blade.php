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
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

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
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])
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

</body>
</html>