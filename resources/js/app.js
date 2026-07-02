import './carousel';
import './search';
import './mobile-menu';
import './player';
import './news';
import './podcasts';
import './aside';

// ════════════════════════════════════════
//  CATEGORY PILLS (Conservé)
// ════════════════════════════════════════
document.querySelectorAll('.pill-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('.pills-bar').querySelectorAll('.pill-btn').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
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