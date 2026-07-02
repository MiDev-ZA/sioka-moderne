(function () {
  const track  = document.getElementById('crslTrack');
  const slides = [...track.querySelectorAll('.crsl-slide')];
  const dots   = [...document.getElementById('crslDots').querySelectorAll('.crsl-dot')];
  const glass  = document.getElementById('crslGlass');
  const elCat  = document.getElementById('crslCat');
  const elLive = document.getElementById('crslLive');
  const elTitle= document.getElementById('crslTitle');
  const elExc  = document.getElementById('crslExcerpt');
  const elMeta = document.getElementById('crslMeta');

  let cur = 0, timer = null, touchX = 0, touchY = 0, dragging = false;

  function updateGlass(idx) {
    const s = slides[idx];
    elCat.textContent    = s.dataset.cat;
    elCat.className      = 'cat-pill ' + (s.dataset.catClass || '');
    elTitle.textContent  = s.dataset.title;
    elExc.textContent    = s.dataset.excerpt;
    elLive.style.display = s.dataset.live === 'true' ? 'flex' : 'none';
    
    elMeta.innerHTML =
      `<span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>${s.dataset.time}</span>` +
      `<span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>${s.dataset.read}</span>` +
      `<span><strong>${s.dataset.author}</strong></span>`;
  }

  function go(n) {
    const total = slides.length;
    cur = ((n % total) + total) % total;

    track.style.transform = `translateX(-${cur * 100}%)`;
    slides.forEach((s, i) => s.classList.toggle('is-active', i === cur));
    dots.forEach((d, i) => {
      d.classList.toggle('active', i === cur);
      d.setAttribute('aria-selected', i === cur ? 'true' : 'false');
    });

    glass.classList.remove('show');
    clearTimeout(glass._t);
    glass._t = setTimeout(() => {
      updateGlass(cur);
      glass.classList.add('show');
    }, 200);
  }

  function resetTimer() {
    clearInterval(timer);
    timer = setInterval(() => go(cur + 1), 6000); 
  }

  updateGlass(0);
  setTimeout(() => glass.classList.add('show'), 300);
  resetTimer();

  document.getElementById('crslPrev').addEventListener('click', () => { go(cur - 1); resetTimer(); });
  document.getElementById('crslNext').addEventListener('click', () => { go(cur + 1); resetTimer(); });
  dots.forEach((d, i) => d.addEventListener('click', () => { go(i); resetTimer(); }));

  const wrap = document.getElementById('carousel');
  wrap.addEventListener('mouseenter', () => clearInterval(timer));
  wrap.addEventListener('mouseleave', () => resetTimer());

  track.addEventListener('touchstart', e => {
    touchX = e.touches[0].clientX;
    touchY = e.touches[0].clientY;
    dragging = false;
  }, { passive: true });
  track.addEventListener('touchmove', e => {
    const dx = Math.abs(e.touches[0].clientX - touchX);
    const dy = Math.abs(e.touches[0].clientY - touchY);
    if (dx > dy && dx > 6) dragging = true;
  }, { passive: true });
  track.addEventListener('touchend', e => {
    if (!dragging) return;
    const dx = e.changedTouches[0].clientX - touchX;
    if (Math.abs(dx) > 38) { go(cur + (dx < 0 ? 1 : -1)); resetTimer(); }
  });

  wrap.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft')  { go(cur - 1); resetTimer(); e.preventDefault(); }
    if (e.key === 'ArrowRight') { go(cur + 1); resetTimer(); e.preventDefault(); }
  });
})();