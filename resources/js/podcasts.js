// Un seul chargement du script Widget API SoundCloud, partagé entre les navigations Turbo
let soundcloudApiPromise = null;

function loadSoundCloudApi() {
    if (window.SC && window.SC.Widget) return Promise.resolve();
    if (soundcloudApiPromise) return soundcloudApiPromise;

    soundcloudApiPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://w.soundcloud.com/player/api.js';
        script.async = true;
        script.onload = () => {
            if (window.SC && window.SC.Widget) resolve();
            else reject(new Error('API SoundCloud indisponible'));
        };
        script.onerror = () => {
            script.remove();
            reject(new Error('Chargement de l\'API SoundCloud impossible'));
        };
        document.head.appendChild(script);
    });

    // En cas d'échec, on autorise une nouvelle tentative au prochain clic
    soundcloudApiPromise.catch(() => { soundcloudApiPromise = null; });
    return soundcloudApiPromise;
}

// Nettoyage de l'initialisation précédente (navigation Turbo)
let teardownPodcasts = null;

document.addEventListener('turbo:load', () => {
    if (teardownPodcasts) {
        teardownPodcasts();
        teardownPodcasts = null;
    }

    const section = document.getElementById('podcasts');
    if (!section) return;

    const PLAY_ICON = '<path d="M8 5v14l11-7z"/>';
    const PAUSE_ICON = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
    const LOAD_TIMEOUT_MS = 15000;

    // Tous les écouteurs de cette initialisation sont retirés d'un coup via ce signal
    const abortController = new AbortController();
    const { signal } = abortController;

    // Déclenche le fondu d'apparition de la vignette "à la une" une fois chargée
    const featImg = section.querySelector('.feat-big img');
    if (featImg) {
        if (featImg.complete) {
            featImg.classList.add('is-loaded');
        } else {
            featImg.addEventListener('load', () => featImg.classList.add('is-loaded'), { signal });
        }
    }

    // Nettoyage d'un éventuel widget laissé par une navigation Turbo précédente
    const stale = document.getElementById('podcastWidget');
    if (stale) stale.remove();

    // --- État du lecteur (source unique de vérité) ---

    let widget = null;
    let widgetFrame = null;
    let loadedUrl = null;   // URL actuellement chargée dans le widget
    let currentUrl = null;  // URL de la piste sélectionnée par l'utilisateur
    let state = 'idle';     // 'idle' | 'loading' | 'playing' | 'paused'
    let progress = 0;       // 0 → 1
    let loadToken = 0;      // invalide les chargements devenus obsolètes
    let loadTimer = null;
    let activeEls = [];

    // --- Rendu de l'interface ---

    const applyItem = (el, itemState, itemProgress) => {
        const isPlaying = itemState === 'playing';

        el.classList.toggle('is-loading', itemState === 'loading');
        el.classList.toggle('is-playing', isPlaying);

        // Accessibilité : le contrôle est l'élément lui-même (à la une) ou son bouton (mini / bibliothèque)
        const control = el.querySelector('.feat-mini-play') || el;
        control.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
        const titleEl = el.querySelector('.feat-big-title, .feat-mini-title');
        const title = titleEl ? titleEl.textContent.trim() : '';
        const verb = isPlaying ? 'Mettre en pause' : 'Écouter';
        control.setAttribute('aria-label', title ? `${verb} : ${title}` : verb);
        if (itemState === 'loading') {
            control.setAttribute('aria-busy', 'true');
        } else {
            control.removeAttribute('aria-busy');
        }

        const svg = el.querySelector('.feat-play-badge svg, .feat-mini-play svg');
        if (svg) {
            const icon = isPlaying ? 'pause' : 'play';
            if (svg.dataset.icon !== icon) {
                svg.innerHTML = isPlaying ? PAUSE_ICON : PLAY_ICON;
                svg.dataset.icon = icon;
            }
        }

        const fill = el.querySelector('.feat-progress-fill');
        if (fill) fill.style.transform = `scaleX(${itemProgress})`;
    };

    const render = () => {
        activeEls = [];
        section.querySelectorAll('[data-soundcloud-url]').forEach((el) => {
            const active = state !== 'idle' && el.dataset.soundcloudUrl === currentUrl;
            if (active) activeEls.push(el);
            applyItem(el, active ? state : 'idle', active ? progress : 0);
        });
    };

    const renderProgress = () => {
        activeEls.forEach((el) => {
            const fill = el.querySelector('.feat-progress-fill');
            if (fill) fill.style.transform = `scaleX(${progress})`;
        });
    };

    // Informe le reste de la page (ex. modale de recherche du header) de l'état du lecteur
    const emitState = () => {
        document.dispatchEvent(new CustomEvent('sioka:podcast-state', {
            detail: { url: state === 'idle' ? null : currentUrl, state },
        }));
    };

    const clearLoadTimer = () => {
        if (loadTimer) {
            clearTimeout(loadTimer);
            loadTimer = null;
        }
    };

    const setState = (nextState, nextProgress = progress) => {
        state = nextState;
        progress = nextProgress;
        if (state !== 'loading') clearLoadTimer();
        render();
        emitState();
    };

    // Évite un spinner infini (autoplay bloqué, réseau coupé, piste introuvable…)
    const startLoadTimer = () => {
        clearLoadTimer();
        loadTimer = setTimeout(() => {
            if (state === 'loading') setState('idle', 0);
        }, LOAD_TIMEOUT_MS);
    };

    const failLoading = () => {
        loadedUrl = null;
        setState('idle', 0);
    };

    // --- Création du widget invisible (une seule fois, sur le premier clic) ---

    function createWidget(url) {
        const iframe = document.createElement('iframe');
        iframe.id = 'podcastWidget';
        iframe.title = 'Lecteur audio SoundCloud';
        iframe.tabIndex = -1;
        iframe.setAttribute('allow', 'autoplay');
        iframe.setAttribute('aria-hidden', 'true');
        Object.assign(iframe.style, {
            position: 'fixed',
            width: '1px',
            height: '1px',
            left: '-9999px',
            bottom: '0',
            border: '0',
            opacity: '0',
            pointerEvents: 'none',
        });
        iframe.src = 'https://w.soundcloud.com/player/?url=' + encodeURIComponent(url) + '&auto_play=true';
        document.body.appendChild(iframe);

        widgetFrame = iframe;
        loadedUrl = url;
        widget = SC.Widget(iframe);

        // Le widget (re)devient prêt après la création et après chaque load() :
        // on relance play() au cas où l'autoplay du paramètre d'URL n'aurait pas suffi.
        widget.bind(SC.Widget.Events.READY, () => {
            if (state === 'loading' && currentUrl === loadedUrl) widget.play();
        });

        widget.bind(SC.Widget.Events.PLAY, () => {
            if (!currentUrl) return;
            setState('playing');
            // La webradio est peut-être en cours de lecture : on lui demande de se mettre en pause
            document.dispatchEvent(new CustomEvent('sioka:podcast-play'));
        });

        // Les événements PAUSE/FINISH de l'ancienne piste, émis pendant un changement, sont ignorés
        widget.bind(SC.Widget.Events.PAUSE, () => {
            if (state === 'playing') setState('paused');
        });

        widget.bind(SC.Widget.Events.FINISH, () => {
            if (state === 'loading') return;
            setState('paused', 0);
        });

        widget.bind(SC.Widget.Events.PLAY_PROGRESS, (data) => {
            if (state !== 'playing') return;
            const value = Number(data && data.relativePosition);
            if (!Number.isFinite(value)) return;
            progress = Math.min(1, Math.max(0, value));
            renderProgress();
        });

        widget.bind(SC.Widget.Events.ERROR, failLoading);
    }

    // Exécute la fonction tout de suite si l'API est déjà là (conserve le geste utilisateur,
    // important pour Safari), sinon après son chargement.
    const withSoundCloud = (callback, token) => {
        if (window.SC && window.SC.Widget) {
            callback();
            return;
        }
        loadSoundCloudApi()
            .then(() => {
                if (token === loadToken) callback();
            })
            .catch(() => {
                if (token === loadToken) failLoading();
            });
    };

    // --- Contrôleur principal ---

    const toggleTrack = (trackUrl) => {
        if (!trackUrl) return;

        // Même piste en cours de chargement : on ignore les clics répétés
        if (trackUrl === currentUrl && state === 'loading') return;

        // Même piste déjà chargée : bascule lecture / pause
        if (trackUrl === currentUrl && state !== 'idle' && widget) {
            widget.isPaused((paused) => {
                if (paused) widget.play();
                else widget.pause();
            });
            return;
        }

        // Nouvelle piste (ou nouvelle tentative après un échec)
        const token = ++loadToken;
        currentUrl = trackUrl;
        setState('loading', 0); // Spinner immédiat en attendant le réseau
        startLoadTimer();

        withSoundCloud(() => {
            if (!widget) {
                createWidget(trackUrl);
            } else if (loadedUrl === trackUrl) {
                widget.play(); // Nouvelle tentative directement dans le geste utilisateur
            } else {
                loadedUrl = trackUrl;
                widget.load(trackUrl, { auto_play: true });
            }
        }, token);
    };

    // Précharge l'API dès la première interaction avec la section
    const preload = () => { loadSoundCloudApi().catch(() => {}); };
    ['pointerenter', 'touchstart', 'focusin'].forEach((eventName) => {
        section.addEventListener(eventName, preload, { once: true, passive: true, signal });
    });

    // --- Délégation d'événements pour toute la zone (inclut la bibliothèque injectée en AJAX) ---

    section.addEventListener('click', (e) => {
        const item = e.target.closest('[data-soundcloud-url]');
        if (!item) return;
        if (e.target.closest('a[href]')) return; // ne détourne pas un vrai lien
        e.preventDefault();
        toggleTrack(item.dataset.soundcloudUrl);
    }, { signal });

    section.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const item = e.target.closest('[data-soundcloud-url]');
        // Les boutons natifs gèrent déjà Entrée/Espace via "click" : évite le double déclenchement
        if (!item || e.target !== item) return;
        e.preventDefault();
        toggleTrack(item.dataset.soundcloudUrl);
    }, { signal });

    // La webradio vient de démarrer : on met le podcast en cours en pause s'il joue
    document.addEventListener('sioka:radio-play', () => {
        if (widget && state === 'playing') widget.pause();
    }, { signal });

    // Demande de lecture venue d'ailleurs dans la page (modale de recherche du header)
    document.addEventListener('sioka:podcast-request', (e) => {
        toggleTrack(e.detail && e.detail.url);
    }, { signal });

    // ---------- Modale : bibliothèque de podcasts (groupée par semaine) ----------

    const libraryBtn = document.getElementById('podcastsLibraryBtn');
    const libraryOverlay = document.getElementById('podcastsLibraryOverlay');
    let closeLibrary = () => {};
    let resetLibrary = () => {};

    if (libraryBtn && libraryOverlay) {
        const libraryBody = document.getElementById('podcastsLibraryBody');
        const libraryClose = document.getElementById('podcastsLibraryClose');
        const loadingMarkup = libraryBody.innerHTML;
        let libraryLoaded = false;
        let libraryLoading = false;

        // Remet la modale dans son état initial (utile avant la mise en cache Turbo)
        resetLibrary = () => {
            libraryBody.innerHTML = loadingMarkup;
            libraryLoaded = false;
            libraryLoading = false;
        };

        const normalizePath = (path) => path.replace(/\/+$/, '');

        const loadLibrary = () => {
            if (libraryLoaded || libraryLoading) return;
            libraryLoading = true;
            libraryBody.innerHTML = loadingMarkup;

            const libraryUrl = libraryBtn.getAttribute('data-library-url');
            const expectedPath = normalizePath(new URL(libraryUrl, window.location.href).pathname);

            fetch(libraryUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                credentials: 'same-origin',
            })
                .then((response) => {
                    // Une redirection (ex. vers une page de connexion) renverrait un 200 avec une page entière :
                    // on la traite comme une erreur au lieu de l'injecter dans la modale.
                    const finalPath = normalizePath(new URL(response.url, window.location.href).pathname);
                    if (!response.ok || finalPath !== expectedPath) throw new Error('Bibliothèque inaccessible');
                    return response.text();
                })
                .then((html) => {
                    libraryBody.innerHTML = html;
                    libraryLoaded = true;
                    render(); // Rétablit l'état visuel de la piste en cours dans la liste
                })
                .catch(() => {
                    libraryBody.innerHTML = '<p class="library-empty">Impossible de charger la bibliothèque pour le moment.</p>';
                })
                .finally(() => { libraryLoading = false; });
        };

        const openLibrary = () => {
            libraryOverlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            if (libraryClose) libraryClose.focus({ preventScroll: true });
            loadLibrary();
        };

        closeLibrary = () => {
            if (!libraryOverlay.classList.contains('is-open')) return;
            libraryOverlay.classList.remove('is-open');
            document.body.style.overflow = '';
            libraryBtn.focus({ preventScroll: true });
        };

        libraryBtn.addEventListener('click', openLibrary, { signal });
        if (libraryClose) libraryClose.addEventListener('click', closeLibrary, { signal });
        libraryOverlay.addEventListener('click', (e) => {
            if (e.target === libraryOverlay) closeLibrary();
        }, { signal });

        document.addEventListener('keydown', (e) => {
            if (!libraryOverlay.classList.contains('is-open')) return;

            if (e.key === 'Escape') {
                closeLibrary();
                return;
            }

            // Garde le focus clavier à l'intérieur de la modale
            if (e.key === 'Tab') {
                const focusables = libraryOverlay.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])');
                if (!focusables.length) return;
                const first = focusables[0];
                const last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                } else if (!libraryOverlay.contains(document.activeElement)) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }, { signal });
    }

    // --- Nettoyage : changement de page Turbo (et page propre dans le cache Turbo) ---

    const cleanup = () => {
        loadToken++;
        clearLoadTimer();
        if (widgetFrame) widgetFrame.remove();
        widget = null;
        widgetFrame = null;
        loadedUrl = null;
        currentUrl = null;
        state = 'idle';
        progress = 0;
        render();
        emitState();
        resetLibrary();
        if (libraryOverlay) libraryOverlay.classList.remove('is-open');
        document.body.style.overflow = '';
        abortController.abort();
    };

    document.addEventListener('turbo:before-cache', cleanup, { signal });
    teardownPodcasts = cleanup;
});