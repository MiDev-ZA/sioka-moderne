
document.addEventListener('turbo:load', () => {
    const section = document.getElementById('podcasts');
    if (!section) return;

    const PLAY_ICON = '<path d="M8 5v14l11-7z"/>';
    const PAUSE_ICON = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';

    // Déclenche le fondu d'apparition de la vignette "à la une" une fois chargée
    const featImg = section.querySelector('.feat-big img');
    if (featImg) {
        if (featImg.complete) {
            featImg.classList.add('is-loaded');
        } else {
            featImg.addEventListener('load', () => featImg.classList.add('is-loaded'));
        }
    }

    // Nettoyage d'un éventuel widget laissé par une navigation Turbo précédente
    const stale = document.getElementById('podcastWidget');
    if (stale) stale.remove();

    let widget = null;
    let widgetPromise = null;
    let currentItem = null; // Élément du DOM en cours de lecture
    let currentUrl = null;

    // --- Fonctions utilitaires UI (identiques à l'ancien lecteur natif) ---

    const resetUI = () => {
        section.querySelectorAll('.is-playing, .is-loading').forEach(el => {
            el.classList.remove('is-playing', 'is-loading');
            el.setAttribute('aria-pressed', 'false');
            const svg = el.querySelector('.feat-play-badge svg, .feat-mini-play svg');
            if (svg) svg.innerHTML = PLAY_ICON;

            const progress = el.querySelector('.feat-progress-fill');
            if (progress) progress.style.transform = 'scaleX(0)';
        });
    };

    const updateItemState = (item, state) => {
        if (!item) return;

        const svg = item.querySelector('.feat-play-badge svg, .feat-mini-play svg');

        item.classList.remove('is-playing', 'is-loading');

        if (state === 'loading') {
            item.classList.add('is-loading');
        } else if (state === 'playing') {
            item.classList.add('is-playing');
            if (svg) svg.innerHTML = PAUSE_ICON;
            item.setAttribute('aria-pressed', 'true');
        } else {
            // En pause ou terminé
            if (svg) svg.innerHTML = PLAY_ICON;
            item.setAttribute('aria-pressed', 'false');
        }
    };

    // --- Chargement paresseux et unique du script Widget API SoundCloud ---

    function getScriptReady() {
        if (widgetPromise) return widgetPromise;
        widgetPromise = new Promise((resolve) => {
            if (window.SC && window.SC.Widget) return resolve();
            const script = document.createElement('script');
            script.src = 'https://w.soundcloud.com/player/api.js';
            script.onload = resolve;
            document.head.appendChild(script);
        });
        return widgetPromise;
    }

    // --- Création du widget invisible (une seule fois, sur le premier clic) ---

    function createWidget(url) {
        const iframe = document.createElement('iframe');
        iframe.id = 'podcastWidget';
        iframe.setAttribute('allow', 'autoplay');
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

        widget = SC.Widget(iframe);

        widget.bind(SC.Widget.Events.PLAY, () => {
            updateItemState(currentItem, 'playing');
            // La webradio est peut-être en cours de lecture : on lui demande de se mettre en pause
            document.dispatchEvent(new CustomEvent('sioka:podcast-play'));
        });

        widget.bind(SC.Widget.Events.PAUSE, () => updateItemState(currentItem, 'paused'));

        widget.bind(SC.Widget.Events.FINISH, () => {
            updateItemState(currentItem, 'paused');
            if (currentItem) {
                const progress = currentItem.querySelector('.feat-progress-fill');
                if (progress) progress.style.transform = 'scaleX(0)';
            }
        });

        widget.bind(SC.Widget.Events.PLAY_PROGRESS, (data) => {
            if (!currentItem) return;
            const progress = currentItem.querySelector('.feat-progress-fill');
            if (progress) {
                progress.style.transform = `scaleX(${data.relativePosition})`;
            }
        });
    }

    // --- Contrôleur Principal ---

    const toggleTrack = (item) => {
        const trackUrl = item.getAttribute('data-soundcloud-url');
        if (!trackUrl) return;

        // Si on clique sur la même piste
        if (currentUrl === trackUrl && widget) {
            widget.isPaused((paused) => {
                if (paused) {
                    widget.play();
                } else {
                    widget.pause();
                }
            });
            return;
        }

        // Si on change de piste
        resetUI();
        currentItem = item;
        currentUrl = trackUrl;
        updateItemState(currentItem, 'loading'); // Spinner immédiat en attendant le réseau

        getScriptReady().then(() => {
            if (!widget) {
                createWidget(trackUrl);
            } else {
                widget.load(trackUrl, { auto_play: true });
            }
        });
    };

    // --- Délégation d'événements pour toute la zone ---

    section.addEventListener('click', (e) => {
        const playableItem = e.target.closest('.feat-big, .feat-mini');
        if (playableItem) {
            e.preventDefault();
            toggleTrack(playableItem);
        }
    });

    section.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            const playableItem = e.target.closest('.feat-big, .feat-mini');
            if (playableItem) {
                e.preventDefault();
                toggleTrack(playableItem);
            }
        }
    });

    // La webradio vient de démarrer : on met le podcast en cours en pause s'il joue
    document.addEventListener('sioka:radio-play', () => {
        if (widget) {
            widget.isPaused((paused) => {
                if (!paused) widget.pause();
            });
        }
    });

    // ---------- Modale : bibliothèque de podcasts (groupée par semaine) ----------

    const libraryBtn = document.getElementById('podcastsLibraryBtn');
    const libraryOverlay = document.getElementById('podcastsLibraryOverlay');

    if (libraryBtn && libraryOverlay) {
        const libraryBody = document.getElementById('podcastsLibraryBody');
        const libraryClose = document.getElementById('podcastsLibraryClose');
        let libraryLoaded = false;

        function openLibrary() {
            libraryOverlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';

            if (!libraryLoaded) {
                const url = libraryBtn.getAttribute('data-library-url');
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => {
                        if (!response.ok) throw new Error('Erreur réseau');
                        return response.text();
                    })
                    .then((html) => {
                        libraryBody.innerHTML = html;
                        libraryLoaded = true;

                        // Rétablit l'état visuel si une piste de la liste est déjà en cours de lecture
                        if (currentUrl) {
                            const activeInList = libraryBody.querySelector(`[data-soundcloud-url="${CSS.escape(currentUrl)}"]`);
                            if (activeInList && currentItem && currentItem.classList.contains('is-playing')) {
                                updateItemState(activeInList, 'playing');
                                currentItem = activeInList;
                            }
                        }
                    })
                    .catch(() => {
                        libraryBody.innerHTML = '<p class="library-empty">Impossible de charger la bibliothèque pour le moment.</p>';
                    });
            }
        }

        function closeLibrary() {
            libraryOverlay.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        libraryBtn.addEventListener('click', openLibrary);
        if (libraryClose) libraryClose.addEventListener('click', closeLibrary);
        libraryOverlay.addEventListener('click', (e) => {
            if (e.target === libraryOverlay) closeLibrary();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && libraryOverlay.classList.contains('is-open')) closeLibrary();
        });
    }
});