/* ==========================================================================
   podcast.js
   Gestionnaire audio natif avec gestion d'états UI fluides (loading, progress).
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('podcasts');
    if (!section) return;

    const PLAY_ICON = '<path d="M8 5v14l11-7z"/>';
    const PAUSE_ICON = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';

    // Initialisation du lecteur natif
    const audio = new Audio();
    audio.id = 'podcastPlayer';
    audio.preload = 'none';

    let currentItem = null; // Élément du DOM en cours de lecture
    let currentUrl = null;

    // --- Fonctions utilitaires UI ---

    const resetUI = () => {
        section.querySelectorAll('.is-playing, .is-loading').forEach(el => {
            el.classList.remove('is-playing', 'is-loading');
            const svg = el.querySelector('.feat-play-badge svg, .feat-mini-play svg');
            if (svg) svg.innerHTML = PLAY_ICON;
            
            const progress = el.querySelector('.feat-progress-fill');
            if (progress) progress.style.transform = 'scaleX(0)';
        });
    };

    const updateItemState = (item, state) => {
        if (!item) return;
        
        const svg = item.querySelector('.feat-play-badge svg, .feat-mini-play svg');
        const btn = item.querySelector('.feat-mini-play');

        item.classList.remove('is-playing', 'is-loading');

        if (state === 'loading') {
            item.classList.add('is-loading');
        } else if (state === 'playing') {
            item.classList.add('is-playing');
            if (svg) svg.innerHTML = PAUSE_ICON;
            if (btn) {
                btn.setAttribute('aria-label', 'Mettre en pause');
                btn.setAttribute('aria-pressed', 'true');
            }
        } else {
            // En pause ou terminé
            if (svg) svg.innerHTML = PLAY_ICON;
            if (btn) {
                btn.setAttribute('aria-label', 'Écouter');
                btn.setAttribute('aria-pressed', 'false');
            }
        }
    };

    // --- Écouteurs d'événements Audio ---

    audio.addEventListener('waiting', () => updateItemState(currentItem, 'loading'));
    
    audio.addEventListener('playing', () => updateItemState(currentItem, 'playing'));
    
    audio.addEventListener('pause', () => updateItemState(currentItem, 'paused'));
    
    audio.addEventListener('ended', () => {
        updateItemState(currentItem, 'paused');
        if (currentItem) {
            const progress = currentItem.querySelector('.feat-progress-fill');
            if (progress) progress.style.transform = 'scaleX(0)';
        }
    });

    audio.addEventListener('timeupdate', () => {
        if (!currentItem || isNaN(audio.duration)) return;
        const percent = audio.currentTime / audio.duration;
        const progress = currentItem.querySelector('.feat-progress-fill');
        if (progress) {
            progress.style.transform = `scaleX(${percent})`;
        }
    });

    // --- Contrôleur Principal ---

    const toggleTrack = (item) => {
        const trackUrl = item.getAttribute('data-track-url');
        if (!trackUrl) return;

        // Si on clique sur la même piste
        if (currentUrl === trackUrl) {
            if (audio.paused) {
                audio.play().catch(e => console.warn('Lecture interrompue', e));
            } else {
                audio.pause();
            }
            return;
        }

        // Si on change de piste
        resetUI();
        currentItem = item;
        currentUrl = trackUrl;
        
        audio.src = trackUrl;
        updateItemState(currentItem, 'loading'); // Spinner immédiat en attendant le réseau
        
        audio.play().catch(() => {
            updateItemState(currentItem, 'paused');
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
});