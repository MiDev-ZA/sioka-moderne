// pour la persistance du lecteur, on doit installer et importer cette bibliothèque
import '@hotwired/turbo';

import './carousel';
import './search';
import './mobile-menu';
import './player';
import './news';
import './podcasts';
import './fil-aside.js';

document.addEventListener('turbo:load', function () {
  // ════════════════════════════════════════
  //  CATEGORY PILLS (Conservé)
  // ════════════════════════════════════════
document.querySelectorAll('.pills-bar').forEach(bar => {
  bar.querySelectorAll('.pill-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      // 1. Etat visuel de la pill active
      bar.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('on'));
      btn.classList.add('on');

      // 2. Filtrage réel des cartes associées à cette barre de filtres
      const grid = bar.nextElementSibling; // .art-grid juste après .pills-bar
      if (!grid) return;

      const filter = btn.dataset.filter;

      grid.querySelectorAll('[data-category]').forEach(card => {
        const wrapper = card.closest('.art-card-link') || card;
        const show = filter === 'all' || card.dataset.category === filter;
        wrapper.style.display = show ? '' : 'none';
      });
    });
  });
});

  const io = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) {
        en.target.classList.remove('paused');
        io.unobserve(en.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.anim').forEach(el => io.observe(el));
});