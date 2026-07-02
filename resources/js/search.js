(function () {
  const overlay = document.getElementById('searchOverlay');
  const input   = document.getElementById('searchInput');

  document.getElementById('searchBtn').addEventListener('click', () => {
    overlay.classList.add('open');
    setTimeout(() => input.focus(), 100);
  });
  document.getElementById('searchClose').addEventListener('click', () => overlay.classList.remove('open'));
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') overlay.classList.remove('open'); });
})();