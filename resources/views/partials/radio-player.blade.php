<div class="radio-player" id="radioPlayer" role="region" aria-label="Lecteur Webradio" data-turbo-permanent>
  <audio id="audioStream" preload="none">
    <source src="https://securestreams.autopo.st:2584/;" type="audio/mpeg">
    Votre navigateur ne supporte pas l'élément audio.
  </audio>

  <div class="rp-shell">

    <div class="rp-info">
      <div class="rp-artwork">
        <div class="rp-disc" id="rpDisc">
          <img
              src="{{ asset('images/SIOKA-LogoOfficiel.png') }}"
              alt="SIOKA"
              class="rp-logo"
          >
        </div>
        <span class="rp-eq" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
      </div>

      <div class="rp-text">
        <span class="rp-live">
          <span class="live-dot"></span> EN DIRECT
        </span>
        <div class="rp-title-wrap">
          <span class="rp-title" id="rpTitle">SIOKA - FM 106 Mhz</span>
        </div>
      </div>
    </div>

    <div class="rp-controls">

      <button class="rp-btn-like" id="likeBtn" type="button" aria-label="Ajouter aux favoris" aria-pressed="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 21s-6.7-4.35-9.3-8.28C1.02 10.1 1.5 6.5 4.6 5.02 7 3.9 9.5 4.9 12 7.5c2.5-2.6 5-3.6 7.4-2.48 3.1 1.48 3.58 5.08 1.9 7.7C18.7 16.65 12 21 12 21z"/>
        </svg>
      </button>

      <div class="rp-volume">
        <button class="rp-vol-icon" id="volIcon" type="button" aria-label="Couper / rétablir le son">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
        </button>
        <div class="rp-slider" id="volSlider" aria-label="Contrôle du volume" role="slider" aria-orientation="vertical" aria-valuemin="0" aria-valuemax="100" aria-valuenow="70" tabindex="0">
          <div class="rp-slider-fill" id="volFill" style="height: 70%;"></div>
        </div>
      </div>

      <button class="rp-btn-play" id="playBtn" type="button" aria-label="Lecture Webradio">
        <span class="rp-btn-play-halo" aria-hidden="true"></span>
        <svg id="iconPlay" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        <svg id="iconPause" style="display:none;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
      </button>

      <button class="rp-btn-share" id="shareBtn" type="button" aria-label="Partager">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
        </svg>
      </button>

    </div>
  </div>
</div>

<script>
(function () {
  var player   = document.getElementById('radioPlayer');
  var audio    = document.getElementById('audioStream');
  var title    = document.getElementById('rpTitle');
  var likeBtn  = document.getElementById('likeBtn');
  var shareBtn = document.getElementById('shareBtn');
  var volIcon  = document.getElementById('volIcon');
  var volFill  = document.getElementById('volFill');
  var volSlider = document.getElementById('volSlider');
  var shell    = player ? player.querySelector('.rp-shell') : null;

  if (!player || !audio) return;

  // Couche purement visuelle : ne remplace pas votre logique play/pause
  // existante, elle écoute juste les évènements natifs de <audio> pour
  // piloter le disque vinyle (qui tourne avec le logo), l'équaliseur,
  // le halo, le point "EN DIRECT" et l'état d'erreur.
  ['play', 'playing'].forEach(function (evt) {
    audio.addEventListener(evt, function () {
      player.classList.add('is-playing');
      player.classList.remove('is-loading', 'has-error');
    });
  });
  audio.addEventListener('pause', function () { player.classList.remove('is-playing'); });
  audio.addEventListener('waiting', function () { player.classList.add('is-loading'); });
  audio.addEventListener('stalled', function () { player.classList.add('is-loading'); });
  audio.addEventListener('error', function () {
    player.classList.remove('is-loading', 'is-playing');
    player.classList.add('has-error');
  });

  // Défilement du titre uniquement s'il déborde réellement de son cadre.
  function checkMarquee() {
    if (!title) return;
    var wrap = title.parentElement;
    title.classList.toggle('is-marquee', title.scrollWidth > wrap.clientWidth + 4);
  }
  checkMarquee();
  window.addEventListener('resize', checkMarquee);
  if (window.MutationObserver && title) {
    new MutationObserver(checkMarquee).observe(title, { childList: true, characterData: true, subtree: true });
  }

  // Favori (visuel) — à brancher sur votre API si vous stockez les favoris.
  if (likeBtn) {
    likeBtn.addEventListener('click', function () {
      var active = likeBtn.classList.toggle('is-active');
      likeBtn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  // Partage natif si disponible, sinon copie du lien dans le presse-papiers.
  if (shareBtn) {
    shareBtn.addEventListener('click', async function () {
      var shareData = {
        title: 'SIOKA - FM 106 Mhz',
        text: title ? title.textContent : 'SIOKA - FM 106 Mhz',
        url: window.location.href
      };
      if (navigator.share) {
        try { await navigator.share(shareData); } catch (e) {}
      } else {
        try {
          await navigator.clipboard.writeText(window.location.href);
          shareBtn.classList.add('is-copied');
          setTimeout(function () { shareBtn.classList.remove('is-copied'); }, 1500);
        } catch (e) {}
      }
    });
  }

  // Volume vertical, toujours en place (pas de popup) : glisser sur la
  // mini-piste, cliquer dessus, ou flèches haut/bas au clavier. La piste
  // (var(--rp-slider-w) dans player.css) ne change jamais de largeur,
  // seule sa hauteur de remplissage varie.
  function syncVolumeIcon() {
    if (!volIcon) return;
    volIcon.classList.toggle('is-muted', audio.muted || audio.volume === 0);
  }

  function applyVolume(ratio) {
    ratio = Math.min(1, Math.max(0, ratio));
    var pct = Math.round(ratio * 100);
    if (volFill) volFill.style.height = pct + '%';
    if (volSlider) volSlider.setAttribute('aria-valuenow', pct);
    audio.volume = ratio;
    if (ratio > 0 && audio.muted) audio.muted = false;
    syncVolumeIcon();
  }

  if (volSlider) {
    var dragging = false;

    var ratioFromClientY = function (clientY) {
      var rect = volSlider.getBoundingClientRect();
      return (rect.bottom - clientY) / rect.height;
    };

    var startDrag = function (e) {
      dragging = true;
      var y = e.touches ? e.touches[0].clientY : e.clientY;
      applyVolume(ratioFromClientY(y));
      e.preventDefault();
    };
    var moveDrag = function (e) {
      if (!dragging) return;
      var y = e.touches ? e.touches[0].clientY : e.clientY;
      applyVolume(ratioFromClientY(y));
    };
    var endDrag = function () { dragging = false; };

    volSlider.addEventListener('mousedown', startDrag);
    volSlider.addEventListener('touchstart', startDrag, { passive: false });
    window.addEventListener('mousemove', moveDrag);
    window.addEventListener('touchmove', moveDrag, { passive: false });
    window.addEventListener('mouseup', endDrag);
    window.addEventListener('touchend', endDrag);

    volSlider.addEventListener('keydown', function (e) {
      var current = parseInt(volSlider.getAttribute('aria-valuenow'), 10) || 0;
      if (e.key === 'ArrowUp' || e.key === 'ArrowRight') { applyVolume((current + 5) / 100); e.preventDefault(); }
      else if (e.key === 'ArrowDown' || e.key === 'ArrowLeft') { applyVolume((current - 5) / 100); e.preventDefault(); }
      else if (e.key === 'Home') { applyVolume(0); e.preventDefault(); }
      else if (e.key === 'End') { applyVolume(1); e.preventDefault(); }
    });
  }

  if (volIcon) {
    volIcon.addEventListener('click', function () {
      audio.muted = !audio.muted;
      syncVolumeIcon();
    });
  }

  syncVolumeIcon();

  // ------------------------------------------------------------------
  // Anti-éclipse du footer : le lecteur est en position:fixed, donc il
  // recouvre en permanence le bas du viewport. On mesure sa hauteur RÉELLE
  // (variable selon le breakpoint, la marge desktop, la safe-area iOS...)
  // et on l'expose via --rp-player-h sur :root. footer.css s'en sert pour
  // ajouter une marge de sécurité et garantir que le contenu du footer
  // reste toujours entièrement visible au scroll, jamais masqué.
  // ------------------------------------------------------------------
  function setPlayerOffset() {
    if (!shell) return;
    var rect = shell.getBoundingClientRect();
    var playerStyles = getComputedStyle(player);
    var paddingBottom = parseFloat(playerStyles.paddingBottom) || 0;
    var comfortMargin = 16; // marge visuelle supplémentaire
    var total = Math.ceil(rect.height + paddingBottom + comfortMargin);
    document.documentElement.style.setProperty('--rp-player-h', total + 'px');
  }

  setPlayerOffset();
  window.addEventListener('resize', setPlayerOffset);
  window.addEventListener('orientationchange', setPlayerOffset);
  if (window.ResizeObserver) {
    new ResizeObserver(setPlayerOffset).observe(player);
  }
})();
</script>