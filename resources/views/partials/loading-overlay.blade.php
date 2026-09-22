<div class="auth-loading-overlay" id="authLoadingOverlay">
  <div class="auth-loading-logo">
    <div class="auth-loading-pulse"></div>
    <div class="auth-loading-pulse"></div>
    <div class="auth-loading-ring"></div>
    <div class="auth-loading-badge">
      <img src="{{ asset('images/icones/favicon-96x96.png') }}" alt="SIOKA" class="auth-loading-badge-img">
    </div>
  </div>

  <div class="auth-loading-text">
    <span id="authLoadingText">Connexion en cours</span>
    <span class="auth-loading-dots"><span>.</span><span>.</span><span>.</span></span>
  </div>
</div>

<script>
  (function () {
    const overlay = document.getElementById('authLoadingOverlay');
    const overlayText = document.getElementById('authLoadingText');
    if (!overlay) return;

    const MIN_VISIBLE_MS = 350;
    let shownAt = 0;

    function showOverlay(label) {
      overlayText.textContent = label || 'Chargement';
      overlay.classList.add('show');
      shownAt = Date.now();
    }

    function hideOverlay() {
      if (!overlay.classList.contains('show')) return;
      const elapsed = Date.now() - shownAt;
      const wait = Math.max(0, MIN_VISIBLE_MS - elapsed);
      setTimeout(function () {
        overlay.classList.remove('show');
      }, wait);
    }

    // ---------- Formulaires (connexion, déconnexion, etc.) ----------
    // Délégation : fonctionne même si le formulaire est recréé par Turbo.
    document.addEventListener('submit', function (e) {
      const form = e.target.closest('form[data-auth-loading]');
      if (!form) return;

      showOverlay(form.dataset.authLoading);

      const btn = form.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
    });

    // ---------- Liens de navigation (ex: menu Actualités) ----------
    // Délégation sur document : reste valide après chaque swap Turbo du sidebar.
    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

      const link = e.target.closest('a[data-page-loading]');
      if (!link || link.target === '_blank') return;

      showOverlay(link.dataset.pageLoading);
    });

    // ---------- Turbo : purger l'overlay avant qu'il ne soit figé dans un snapshot ----------
    document.addEventListener('turbo:before-cache', function () {
      overlay.classList.remove('show');
    });

    // ---------- Turbo : masquer une fois la nouvelle page rendue (avec durée minimale) ----------
    document.addEventListener('turbo:load', hideOverlay);

    // ---------- Sécurité : cache arrière natif du navigateur (bfcache) ----------
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) overlay.classList.remove('show');
    });
  })();
</script>