// Lecteur webradio SIOKA.
// Tout le comportement du lecteur vit ici (lecture, volume, favori, partage, décalage du footer).
// Le bloc <script> inline de radio-player.blade.php a été supprimé : avec Turbo il était ré-exécuté
// à chaque navigation et empilait des écouteurs sur l'élément <audio> persistant (data-turbo-permanent),
// ce qui annulait par exemple le bouton muet (double bascule par clic).
(function () {
  'use strict';

  var MAX_RETRIES = 3;
  var RETRY_DELAY_MS = 1500;
  var DEFAULT_VOLUME = 0.7;
  var STORAGE_VOLUME = 'sioka:radio-volume';
  var STORAGE_MUTED = 'sioka:radio-muted';

  function readStorage(key) {
    try { return window.localStorage.getItem(key); } catch (e) { return null; }
  }
  function writeStorage(key, value) {
    try { window.localStorage.setItem(key, value); } catch (e) { /* stockage indisponible */ }
  }

  function init() {
    var player = document.getElementById('radioPlayer');
    var audio = document.getElementById('audioStream');
    var playBtn = document.getElementById('playBtn');
    if (!player || !audio || !playBtn) return;

    // Idempotent : le lecteur est persistant (data-turbo-permanent), on ne l'initialise qu'une fois
    if (player.dataset.rpReady === '1') return;
    player.dataset.rpReady = '1';

    var iconPlay = document.getElementById('iconPlay');
    var iconPause = document.getElementById('iconPause');
    var title = document.getElementById('rpTitle');
    var likeBtn = document.getElementById('likeBtn');
    var shareBtn = document.getElementById('shareBtn');
    var volIcon = document.getElementById('volIcon');
    var volFill = document.getElementById('volFill');
    var volSlider = document.getElementById('volSlider');
    var logo = player.querySelector('.rp-logo');
    var shell = player.querySelector('.rp-shell');

    var LIVE_TITLE = (title && title.textContent.trim()) || 'SIOKA - FM 106 Mhz';
    var PAUSED_TITLE = 'Webradio en pause';
    var ERROR_TITLE = 'Erreur de connexion au flux';

    // ------------------------------------------------------------------
    // État : l'interface est toujours dérivée de ces variables, jamais écrite
    // "à l'avance". Si la lecture échoue, l'écran ne reste donc pas bloqué sur "pause".
    // ------------------------------------------------------------------
    var state = 'idle';      // 'idle' | 'loading' | 'playing' | 'paused' | 'error'
    var intent = false;      // l'auditeur veut que la radio joue
    var retries = 0;
    var retryTimer = null;
    var playToken = 0;
    var failedWhileWanting = false; // échec de reconnexion alors que l'auditeur voulait écouter

    function setTitle(text) {
      if (title && title.textContent !== text) title.textContent = text;
    }

    function render() {
      player.classList.toggle('is-loading', state === 'loading');
      player.classList.toggle('is-playing', state === 'playing');
      player.classList.toggle('has-error', state === 'error');

      playBtn.classList.toggle('playing', intent);
      if (iconPlay) iconPlay.style.display = intent ? 'none' : 'block';
      if (iconPause) iconPause.style.display = intent ? 'block' : 'none';
      playBtn.setAttribute('aria-label', intent ? 'Mettre la webradio en pause' : 'Lecture Webradio');
      if (state === 'loading') playBtn.setAttribute('aria-busy', 'true');
      else playBtn.removeAttribute('aria-busy');

      if (state === 'paused') setTitle(PAUSED_TITLE);
      else if (state === 'error') setTitle(ERROR_TITLE);
      else setTitle(LIVE_TITLE);

      if ('mediaSession' in navigator) {
        navigator.mediaSession.playbackState = state === 'playing' ? 'playing' : (intent ? 'playing' : 'paused');
      }
    }

    function setState(next) {
      state = next;
      render();
    }

    // ------------------------------------------------------------------
    // Lecture / pause
    // ------------------------------------------------------------------

    // Flux en direct : on recharge toujours la source avant de lire, sinon la reprise
    // rejouerait l'audio mis en mémoire au moment de la pause au lieu du direct.
    function startStream() {
      var attempt = ++playToken;
      audio.load();
      var promise = audio.play();
      if (promise && typeof promise.catch === 'function') {
        promise.catch(function (err) {
          if (attempt !== playToken || !intent) return; // remplacé par une action plus récente
          if (err && err.name === 'AbortError') return; // interrompu par une pause / un rechargement
          if (err && err.name === 'NotAllowedError') {
            intent = false;
            setState('error');
            return;
          }
          scheduleReconnect();
        });
      }
    }

    function scheduleReconnect() {
      clearTimeout(retryTimer);
      if (!intent) return;
      if (retries >= MAX_RETRIES) {
        intent = false;
        failedWhileWanting = true;
        setState('error');
        return;
      }
      retries++;
      setState('loading');
      retryTimer = setTimeout(function () {
        if (intent) startStream();
      }, RETRY_DELAY_MS * retries);
    }

    function start() {
      intent = true;
      failedWhileWanting = false;
      retries = 0;
      clearTimeout(retryTimer);
      // Un podcast est peut-être en cours de lecture : on lui demande de se mettre en pause
      document.dispatchEvent(new CustomEvent('sioka:radio-play'));
      setState('loading');
      startStream();
    }

    function stop() {
      intent = false;
      failedWhileWanting = false;
      playToken++;
      clearTimeout(retryTimer);
      audio.pause();
      audio.load(); // coupe la connexion au flux au lieu de continuer à le télécharger en tâche de fond
      setState('paused');
    }

    function toggle() {
      if (intent) stop();
      else start();
    }

    // Conservé pour compatibilité avec d'éventuels appels externes
    window.togglePlay = toggle;
    playBtn.addEventListener('click', toggle);

    // Un podcast vient de démarrer : on met la webradio en pause si elle joue
    document.addEventListener('sioka:podcast-play', function () {
      if (intent) stop();
    });

    // ------------------------------------------------------------------
    // Événements de l'élément <audio>
    // ------------------------------------------------------------------

    audio.addEventListener('playing', function () {
      if (!intent) return;
      retries = 0;
      setState('playing');
    });

    audio.addEventListener('waiting', function () {
      if (intent) setState('loading');
    });

    audio.addEventListener('stalled', function () {
      if (intent && audio.readyState < 3) setState('loading');
    });

    // Filet de sécurité : sortie du spinner si "playing" n'a pas été ré-émis après une mise en mémoire tampon
    audio.addEventListener('timeupdate', function () {
      if (intent && state === 'loading' && !audio.paused && audio.readyState >= 3) setState('playing');
    });

    // Pause déclenchée hors de l'interface (touches média, casque, système) : on synchronise l'écran
    audio.addEventListener('pause', function () {
      if (audio.ended) return; // une fin de flux est gérée par "ended"
      if (intent) {
        intent = false;
        clearTimeout(retryTimer);
        setState('paused');
      }
    });

    // Un flux en direct ne devrait jamais se terminer : on tente de se reconnecter
    audio.addEventListener('ended', function () {
      if (intent) scheduleReconnect();
    });

    // Avec <source>, l'erreur est émise sur l'élément <source> et ne remonte pas jusqu'à <audio> :
    // on l'écoute en phase de capture pour ne pas la manquer.
    audio.addEventListener('error', function () {
      if (intent) scheduleReconnect();
    }, true);

    // Retour du réseau : on relance seulement si l'auditeur voulait écouter et que la reconnexion avait échoué
    window.addEventListener('online', function () {
      if (failedWhileWanting) start();
    });

    // ------------------------------------------------------------------
    // Touches média / écran de verrouillage
    // ------------------------------------------------------------------
    if ('mediaSession' in navigator) {
      try {
        navigator.mediaSession.metadata = new MediaMetadata({
          title: LIVE_TITLE,
          artist: 'SIOKA',
          album: 'Webradio',
          artwork: logo ? [{ src: logo.currentSrc || logo.src }] : []
        });
        navigator.mediaSession.setActionHandler('play', function () { if (!intent) start(); });
        navigator.mediaSession.setActionHandler('pause', function () { if (intent) stop(); });
        navigator.mediaSession.setActionHandler('stop', function () { if (intent) stop(); });
      } catch (e) { /* API partiellement supportée */ }
    }

    // ------------------------------------------------------------------
    // Volume
    // ------------------------------------------------------------------

    function syncVolumeUi() {
      var muted = audio.muted || audio.volume === 0;
      if (volIcon) {
        volIcon.classList.toggle('is-muted', muted);
        volIcon.setAttribute('aria-pressed', muted ? 'true' : 'false');
      }
      var pct = Math.round(audio.volume * 100);
      if (volFill) volFill.style.height = pct + '%';
      if (volSlider) volSlider.setAttribute('aria-valuenow', String(pct));
    }

    function applyVolume(ratio) {
      ratio = Math.min(1, Math.max(0, ratio));
      audio.volume = ratio;
      if (ratio > 0 && audio.muted) audio.muted = false;
      writeStorage(STORAGE_VOLUME, String(ratio));
      writeStorage(STORAGE_MUTED, audio.muted ? '1' : '0');
    }

    audio.addEventListener('volumechange', syncVolumeUi);

    // Volume initial : le curseur affichait 70 % alors que l'audio démarrait à 100 %
    var storedVolume = parseFloat(readStorage(STORAGE_VOLUME));
    audio.volume = isFinite(storedVolume) ? Math.min(1, Math.max(0, storedVolume)) : DEFAULT_VOLUME;
    audio.muted = readStorage(STORAGE_MUTED) === '1';
    syncVolumeUi();

    if (volIcon) {
      volIcon.addEventListener('click', function () {
        audio.muted = !audio.muted;
        writeStorage(STORAGE_MUTED, audio.muted ? '1' : '0');
      });
    }

    if (volSlider) {
      var dragging = false;

      var ratioFromClientY = function (clientY) {
        var rect = volSlider.getBoundingClientRect();
        return rect.height ? (rect.bottom - clientY) / rect.height : 0;
      };

      volSlider.addEventListener('pointerdown', function (e) {
        dragging = true;
        try { volSlider.setPointerCapture(e.pointerId); } catch (err) { /* ignoré */ }
        volSlider.focus({ preventScroll: true });
        applyVolume(ratioFromClientY(e.clientY));
        e.preventDefault();
      });
      volSlider.addEventListener('pointermove', function (e) {
        if (dragging) applyVolume(ratioFromClientY(e.clientY));
      });
      var endDrag = function () { dragging = false; };
      volSlider.addEventListener('pointerup', endDrag);
      volSlider.addEventListener('pointercancel', endDrag);
      volSlider.addEventListener('lostpointercapture', endDrag);

      volSlider.addEventListener('keydown', function (e) {
        var current = Math.round(audio.volume * 100);
        var next = null;
        if (e.key === 'ArrowUp' || e.key === 'ArrowRight') next = current + 5;
        else if (e.key === 'ArrowDown' || e.key === 'ArrowLeft') next = current - 5;
        else if (e.key === 'PageUp') next = current + 10;
        else if (e.key === 'PageDown') next = current - 10;
        else if (e.key === 'Home') next = 0;
        else if (e.key === 'End') next = 100;
        if (next === null) return;
        e.preventDefault();
        applyVolume(next / 100);
      });
    }

    // ------------------------------------------------------------------
    // Favori (visuel) et partage
    // ------------------------------------------------------------------

    if (likeBtn) {
      likeBtn.addEventListener('click', function () {
        var active = likeBtn.classList.toggle('is-active');
        likeBtn.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    }

    if (shareBtn) {
      var sharing = false;
      shareBtn.addEventListener('click', async function () {
        if (sharing) return;
        sharing = true;
        var url = window.location.href;
        try {
          if (navigator.share) {
            await navigator.share({ title: LIVE_TITLE, text: LIVE_TITLE, url: url });
          } else if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(url);
            shareBtn.classList.add('is-copied');
            setTimeout(function () { shareBtn.classList.remove('is-copied'); }, 1500);
          }
        } catch (e) { /* partage annulé par l'utilisateur ou indisponible */ }
        sharing = false;
      });
    }

    // ------------------------------------------------------------------
    // Défilement du titre s'il déborde réellement de son cadre
    // ------------------------------------------------------------------

    function checkMarquee() {
      if (!title || !title.parentElement) return;
      title.classList.toggle('is-marquee', title.scrollWidth > title.parentElement.clientWidth + 4);
    }
    checkMarquee();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(checkMarquee);
    window.addEventListener('resize', checkMarquee);
    if (window.MutationObserver && title) {
      new MutationObserver(checkMarquee).observe(title, { childList: true, characterData: true, subtree: true });
    }

    // ------------------------------------------------------------------
    // Anti-éclipse du footer : le lecteur est en position:fixed et recouvre le bas du viewport.
    // On mesure sa hauteur réelle et on l'expose via --rp-player-h (utilisée par footer.css).
    // ------------------------------------------------------------------

    function setPlayerOffset() {
      if (!shell) return;
      var rect = shell.getBoundingClientRect();
      var paddingBottom = parseFloat(getComputedStyle(player).paddingBottom) || 0;
      var comfortMargin = 16;
      var total = Math.ceil(rect.height + paddingBottom + comfortMargin);
      document.documentElement.style.setProperty('--rp-player-h', total + 'px');
    }
    setPlayerOffset();
    window.addEventListener('resize', setPlayerOffset);
    window.addEventListener('orientationchange', setPlayerOffset);
    if (window.ResizeObserver) new ResizeObserver(setPlayerOffset).observe(player);

    render();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
  // Si le lecteur apparaît après une navigation Turbo (layout différent), on l'initialise aussi
  document.addEventListener('turbo:load', init);
})();