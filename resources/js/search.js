document.addEventListener('DOMContentLoaded', () => {
  (function () {
    const overlay     = document.getElementById('searchOverlay');
    const input       = document.getElementById('searchInput');
    const searchBtn   = document.getElementById('searchBtn');
    const searchClose = document.getElementById('searchClose');
  
    // On vérifie que le bouton d'ouverture et l'overlay existent
    if (searchBtn && overlay) {
      searchBtn.addEventListener('click', () => {
        overlay.classList.add('open');
        // On vérifie que le champ de saisie existe avant d'y mettre le focus
        if (input) setTimeout(() => input.focus(), 100);
      });
    }

    // On vérifie que le bouton de fermeture existe
    if (searchClose && overlay) {
      searchClose.addEventListener('click', () => overlay.classList.remove('open'));
    }

    // Sécurisation des événements globaux liés à l'overlay
    if (overlay) {
      overlay.addEventListener('click', e => { 
        if (e.target === overlay) overlay.classList.remove('open'); 
      });

      document.addEventListener('keydown', e => { 
        if (e.key === 'Escape') overlay.classList.remove('open'); 
      });
    }
  })();
});
