let tickerInterval = null;

document.addEventListener('turbo:load', () => {
    // On stoppe l'intervalle de la navigation précédente avant d'en créer un nouveau
    if (tickerInterval) {
        clearInterval(tickerInterval);
        tickerInterval = null;
    }

    const tickerList = document.getElementById('verticalTicker');
    if (!tickerList) return;

    // S'il y a trop peu d'éléments, pas besoin d'animer
    if (tickerList.children.length <= 3) return;

    // Configuration de l'animation
    const transitionStyle = 'transform 0.35s cubic-bezier(0.25, 1, 0.5, 1)';
    tickerList.style.transition = transitionStyle;

    function slideNext() {
        // On récupère le premier élément et sa hauteur dynamique
        const firstItem = tickerList.children[0];
        const itemHeight = firstItem.getBoundingClientRect().height;

        // 1. On glisse toute la liste vers le haut
        tickerList.style.transform = `translateY(-${itemHeight}px)`;

        // 2. On attend la fin de la transition (350ms) pour faire le "tour de magie"
        setTimeout(() => {
            // On désactive la transition pour qu'on ne voie pas le mouvement
            tickerList.style.transition = 'none';

            // On déplace le premier élément à la fin de la liste (le DOM se met à jour)
            tickerList.appendChild(firstItem);

            // On remet la position à 0 instantanément
            tickerList.style.transform = 'translateY(0)';

            // On réactive la transition pour le prochain tour
            setTimeout(() => {
                tickerList.style.transition = transitionStyle;
            }, 50); // Un léger délai pour que le navigateur applique bien le style

        }, 250); // 350ms = durée de la transition CSS, doit rester synchronisé avec transitionStyle

    }

    // Lance le défilement toutes les 4 secondes
    tickerInterval = setInterval(slideNext, 4000);

    // Sécurité : Mettre en pause lors du survol pour permettre le clic
    const windowContainer = document.querySelector('.news-feed-window');
    if (windowContainer) {
        windowContainer.addEventListener('mouseenter', () => clearInterval(tickerInterval));
        windowContainer.addEventListener('mouseleave', () => {
            tickerInterval = setInterval(slideNext, 4000);
        });
    }
});