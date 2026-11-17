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
        <div class="rp-title-wrap" aria-live="polite">
          <span class="rp-title" id="rpTitle">SIOKA - FM 106 Mhz</span>
        </div>
      </div>
    </div>

    <div class="rp-controls">

      <button class="rp-btn-like" id="likeBtn" type="button" aria-label="Ajouter aux favoris" aria-pressed="false">
        <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 21s-6.7-4.35-9.3-8.28C1.02 10.1 1.5 6.5 4.6 5.02 7 3.9 9.5 4.9 12 7.5c2.5-2.6 5-3.6 7.4-2.48 3.1 1.48 3.58 5.08 1.9 7.7C18.7 16.65 12 21 12 21z"/>
        </svg>
      </button>

      <div class="rp-volume">
        <button class="rp-vol-icon" id="volIcon" type="button" aria-label="Couper / rétablir le son">
          <svg aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
        </button>
        <div class="rp-slider" id="volSlider" aria-label="Contrôle du volume" role="slider" aria-orientation="vertical" aria-valuemin="0" aria-valuemax="100" aria-valuenow="70" tabindex="0">
          <div class="rp-slider-fill" id="volFill" style="height: 70%;"></div>
        </div>
      </div>

      <button class="rp-btn-play" id="playBtn" type="button" aria-label="Lecture Webradio">
        <span class="rp-btn-play-halo" aria-hidden="true"></span>
        <svg aria-hidden="true" focusable="false" id="iconPlay" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        <svg aria-hidden="true" focusable="false" id="iconPause" style="display:none;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
      </button>

      <button class="rp-btn-share" id="shareBtn" type="button" aria-label="Partager">
        <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
        </svg>
      </button>

    </div>
  </div>

  <script>
    // Statistiques : une écoute est comptée à chaque démarrage de la lecture (pas à la pause)
    (function () {
      var audio = document.getElementById('audioStream');
      if (!audio || audio.dataset.tracked) { return; }
      audio.dataset.tracked = '1';
      audio.addEventListener('play', function () {
        if (typeof siokaTrack === 'function') { siokaTrack('radio_play', { item_title: 'SIOKA Webradio' }); }
      });
    })();
  </script>
</div>