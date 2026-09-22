@if($flashInfos->isNotEmpty())
<div class="ticker" role="marquee" aria-label="Actualités en direct SIOKA">
  <div class="ticker-inner">
    <div class="ticker-label">
      <span class="ticker-pulse"></span>
      FLASH
    </div>
    <div class="ticker-scroll">
      <div class="ticker-track" data-ticker-track>
        @foreach($flashInfos as $flash)
          <span class="ticker-item"><strong>{{ $flash->category->name ?? 'Actualité' }}</strong> : {{ $flash->content }}</span>
        @endforeach

        {{-- Duplication pour boucle continue sans coupure --}}
        @foreach($flashInfos as $flash)
          <span class="ticker-item" aria-hidden="true"><strong>{{ $flash->category->name ?? 'Actualité' }}</strong> : {{ $flash->content }}</span>
        @endforeach
      </div>
    </div>
  </div>
</div>

@once
<script>
  (function () {
    // Vitesse constante souhaitée, en pixels par seconde. Ajuste cette valeur.
    var TICKER_SPEED_PX_PER_SEC = 80;

    function setTickerDuration(track) {
      // scrollWidth = largeur totale (contenu dupliqué inclus).
      // On divise par 2 car l'animation ne parcourt que -50% (la moitié, non dupliquée).
      var distance = track.scrollWidth / 2;
      if (!distance) return;
      var duration = distance / TICKER_SPEED_PX_PER_SEC;
      track.style.animationDuration = duration + 's';
    }

    function initAllTickers() {
      document.querySelectorAll('[data-ticker-track]').forEach(setTickerDuration);
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initAllTickers);
    } else {
      initAllTickers();
    }

    // Recalcule si la fenêtre est redimensionnée (le texte peut changer de ligne / largeur dispo change)
    var resizeTimeout;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(initAllTickers, 200);
    });

    // Recalcule aussi si les polices se chargent après coup (largeur du texte peut changer)
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(initAllTickers);
    }
  })();
</script>
@endonce
@endif