document.addEventListener('DOMContentLoaded', () => {
  const filterButtons = document.querySelectorAll('#newsFilters .pill-btn');
  const articles = document.querySelectorAll('#newsGrid .art-card');

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      // 1. Gérer la classe active sur les boutons de filtrage
      filterButtons.forEach(btn => btn.classList.remove('on'));
      button.classList.add('on');

      // 2. Filtrer les articles avec une transition propre
      const filterValue = button.getAttribute('data-filter');

      articles.forEach(article => {
        const category = article.getAttribute('data-category');
        
        if (filterValue === 'all' || category === filterValue) {
          // Affiche l'article
          article.style.display = 'flex';
          // Optionnel : relancer proprement l'animation si nécessaire
          article.classList.remove('paused');
        } else {
          // Masque l'article
          article.style.display = 'none';
        }
      });
    });
  });
});