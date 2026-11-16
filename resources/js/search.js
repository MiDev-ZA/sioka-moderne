// Recherche du header : modale + recherche en direct (articles, vidéos archives, podcasts).
// Les écouteurs sont posés sur `document` (délégation) : ils survivent aux remplacements
// de <body> faits par Hotwire Turbo, contrairement à DOMContentLoaded.
(function () {
  // Si ce script est exécuté plusieurs fois (rechargement à chaud, double import),
  // on retire les écouteurs de l'exécution précédente : il n'en reste jamais qu'un jeu.
  if (window.__siokaSearchAbort) window.__siokaSearchAbort.abort();
  const lifecycle = new AbortController();
  window.__siokaSearchAbort = lifecycle;
  const { signal } = lifecycle;
  window.__siokaSearchReady = true;

  const MIN_CHARS = 2;
  const DEBOUNCE_MS = 250;
  const STAGGER_MAX = 6;

  const IDLE_TEXT = 'Tapez au moins 2 caractères pour chercher un article, une vidéo ou un podcast.';
  const ERROR_TEXT = 'La recherche est momentanément indisponible. Réessayez dans un instant.';

  const PLAY_PATH = 'M8 5v14l11-7z';
  const PAUSE_PATH = 'M6 19h4V5H6v14zm8-14v14h4V5h-4z';

  let timer = null;
  let controller = null;
  let lastFocus = null;
  let podcastState = { url: null, state: 'idle' }; // état du lecteur de la section Podcasts

  const $ = (id) => document.getElementById(id);

  // ── Ouverture / fermeture ─────────────────────────────────
  function openSearch() {
    const overlay = $('searchOverlay');
    if (!overlay) {
      console.warn('[SIOKA recherche] #searchOverlay est introuvable sur cette page : le partial search-modal n\'est pas inclus dans son layout.');
      return;
    }

    lastFocus = document.activeElement;
    overlay.classList.add('open');
    document.documentElement.classList.add('search-open');
    $('searchBtn')?.setAttribute('aria-expanded', 'true');
    setTimeout(() => $('searchInput')?.focus(), 80);
  }

  function closeSearch(restoreFocus = true) {
    const overlay = $('searchOverlay');
    clearTimeout(timer);
    controller?.abort();
    document.documentElement.classList.remove('search-open');
    $('searchBtn')?.setAttribute('aria-expanded', 'false');
    $('searchBox')?.classList.remove('is-loading');

    if (!overlay || !overlay.classList.contains('open')) return;
    overlay.classList.remove('open');

    if (restoreFocus && lastFocus && document.contains(lastFocus)) lastFocus.focus();
    lastFocus = null;
  }

  function resetSearch() {
    const input = $('searchInput');
    if (input) input.value = '';
    showStatus(IDLE_TEXT);
  }

  // ── Rendu ─────────────────────────────────────────────────
  const fold = (s) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  const escapeRegExp = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

  // Texte avec les mots recherchés surlignés (insensible à la casse et aux accents).
  function highlight(text, query) {
    const frag = document.createDocumentFragment();
    const hay = fold(text);
    const terms = fold(query).split(/\s+/).filter(Boolean);

    if (!terms.length || hay.length !== text.length) {
      frag.append(text);
      return frag;
    }

    const re = new RegExp(terms.map(escapeRegExp).join('|'), 'g');
    let last = 0;
    let m;
    while ((m = re.exec(hay)) !== null) {
      if (m[0] === '') { re.lastIndex++; continue; }
      if (m.index > last) frag.append(text.slice(last, m.index));
      const mark = document.createElement('mark');
      mark.textContent = text.slice(m.index, m.index + m[0].length);
      frag.append(mark);
      last = m.index + m[0].length;
    }
    if (last < text.length) frag.append(text.slice(last));
    return frag;
  }

  function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
  }

  function playIcon() {
    const wrap = el('span', 'search-play');
    wrap.setAttribute('aria-hidden', 'true');
    wrap.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
    return wrap;
  }

  // Reflète l'état du lecteur (lecture, pause, chargement) sur un résultat podcast
  function applyPodcastState(link) {
    const url = link.dataset.podcastUrl;
    const active = !!url && podcastState.state !== 'idle' && url === podcastState.url;
    const playing = active && podcastState.state === 'playing';

    link.classList.toggle('is-playing', playing);
    link.classList.toggle('is-loading', active && podcastState.state === 'loading');
    link.querySelector('.search-play path')?.setAttribute('d', playing ? PAUSE_PATH : PLAY_PATH);
  }

  function buildResult(item, type, query, index) {
    const link = el('a', 'search-result search-result--' + type);
    link.href = item.url;
    if (index < STAGGER_MAX) link.style.animationDelay = index * 35 + 'ms';

    if (item.image || type === 'video' || type === 'podcast') {
      const thumb = el('span', 'search-thumb');
      if (item.image) {
        const img = document.createElement('img');
        img.src = item.image;
        img.alt = '';
        img.loading = 'lazy';
        thumb.append(img);
      }
      if (type === 'video' || type === 'podcast') thumb.append(playIcon());
      link.append(thumb);
    }

    const body = el('span', 'search-result-body');

    if (item.category || item.date) {
      const meta = el('span', 'search-result-meta');
      if (item.category) meta.append(el('span', 'search-result-cat', item.category));
      if (item.date) meta.append(el('span', 'search-result-date', item.date));
      body.append(meta);
    }

    const title = el('span', 'search-result-title');
    const titleText = el('span', 'search-result-title-text');
    titleText.append(highlight(item.title || '', query));
    title.append(titleText);
    body.append(title);

    if (item.summary) {
      const excerpt = el('span', 'search-result-excerpt');
      excerpt.append(highlight(item.summary, query));
      body.append(excerpt);
    }

    link.append(body);

    if (type === 'podcast' && item.soundcloud_url) {
      link.dataset.podcastUrl = item.soundcloud_url;
      applyPodcastState(link);
    }

    return link;
  }

  function buildGroup(label, type, items, query) {
    const group = el('section', 'search-group search-group--' + type);
    const head = el('h3', 'search-group-title');
    head.append(el('span', null, label), el('span', 'search-group-count', String(items.length)));
    group.append(head);
    items.forEach((item, i) => group.append(buildResult(item, type, query, i)));
    return group;
  }

  function showStatus(text) {
    const box = $('searchResults');
    if (!box) return;
    box.replaceChildren(el('p', 'search-status', text));
  }

  function showSkeleton() {
    const box = $('searchResults');
    if (!box) return;
    const wrap = el('div', 'search-skeleton');
    wrap.setAttribute('aria-hidden', 'true');
    for (let i = 0; i < 3; i++) {
      const row = el('div', 'search-skel-row');
      row.append(el('span', 'search-skel-thumb'));
      const lines = el('span', 'search-skel-lines');
      lines.append(el('span', 'search-skel-line'), el('span', 'search-skel-line search-skel-line--short'));
      row.append(lines);
      wrap.append(row);
    }
    box.replaceChildren(wrap);
  }

  function render(data, query) {
    const box = $('searchResults');
    if (!box) return;

    const articles = Array.isArray(data.articles) ? data.articles : [];
    const videos = Array.isArray(data.videos) ? data.videos : [];
    const podcasts = Array.isArray(data.podcasts) ? data.podcasts : [];

    if (!articles.length && !videos.length && !podcasts.length) {
      showStatus('Aucun résultat pour « ' + query + ' ». Essayez un autre mot-clé.');
      return;
    }

    const frag = document.createDocumentFragment();
    if (articles.length) frag.append(buildGroup('Articles', 'article', articles, query));
    if (videos.length) frag.append(buildGroup('Vidéos archives', 'video', videos, query));
    if (podcasts.length) frag.append(buildGroup('Podcasts', 'podcast', podcasts, query));
    box.replaceChildren(frag);
    box.scrollTop = 0;
  }

  // ── Requête ───────────────────────────────────────────────
  async function runSearch(query) {
    const overlay = $('searchOverlay');
    const endpoint = overlay?.dataset.endpoint;
    if (!endpoint) return;

    controller?.abort();
    const mine = (controller = new AbortController());

    const box = $('searchBox');
    box?.classList.add('is-loading');
    if (!$('searchResults')?.querySelector('.search-group')) showSkeleton();

    try {
      const url = endpoint + (endpoint.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(query);
      const res = await fetch(url, {
        signal: mine.signal,
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      render(await res.json(), query);
    } catch (err) {
      if (err.name === 'AbortError') return;
      showStatus(ERROR_TEXT);
    } finally {
      if (controller === mine) $('searchBox')?.classList.remove('is-loading');
    }
  }

  function onInput(input) {
    const query = input.value.trim();
    clearTimeout(timer);

    if (query.length < MIN_CHARS) {
      controller?.abort();
      $('searchBox')?.classList.remove('is-loading');
      showStatus(IDLE_TEXT);
      return;
    }
    timer = setTimeout(() => runSearch(query), DEBOUNCE_MS);
  }

  // ── Navigation clavier dans les résultats ─────────────────
  function moveFocus(direction) {
    const items = Array.from(document.querySelectorAll('#searchResults .search-result'));
    if (!items.length) return;

    const index = items.indexOf(document.activeElement);
    if (index === -1) {
      if (direction > 0) items[0].focus();
      return;
    }
    const next = index + direction;
    if (next < 0) $('searchInput')?.focus();
    else if (next < items.length) items[next].focus();
  }

  // ── Écouteurs globaux  ──────────────
  document.addEventListener('click', (e) => {
    if (e.target.closest('#searchBtn')) {
      e.preventDefault();
      openSearch();
    } else if (e.target.closest('#searchClose') || e.target === $('searchOverlay')) {
      closeSearch();
    } else if (e.target.closest('#searchResults .search-result--podcast[data-podcast-url]') && document.getElementById('podcasts')) {
      // Sur l'accueil, le lecteur de la section Podcasts existe : on lance ou on met en pause directement.
      // Ailleurs, le lien emmène vers la section Podcasts de l'accueil.
      e.preventDefault();
      document.dispatchEvent(new CustomEvent('sioka:podcast-request', {
        detail: { url: e.target.closest('.search-result--podcast').dataset.podcastUrl },
      }));
    } else if (e.target.closest('#searchResults .search-result')) {
      closeSearch(false);
    }
  }, { signal, capture: true });

  document.addEventListener('input', (e) => {
    if (e.target.id === 'searchInput') onInput(e.target);
  }, { signal });

  document.addEventListener('keydown', (e) => {
    if (!$('searchOverlay')?.classList.contains('open')) return;

    if (e.key === 'Escape') {
      closeSearch();
    } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      moveFocus(e.key === 'ArrowDown' ? 1 : -1);
    }
  }, { signal });

  document.addEventListener('sioka:podcast-state', (e) => {
    podcastState = { url: e.detail?.url ?? null, state: e.detail?.state ?? 'idle' };
    document.querySelectorAll('#searchResults .search-result--podcast').forEach(applyPodcastState);
  }, { signal });

  // Turbo : on ferme avant de quitter la page, et on nettoie le snapshot mis en cache.
  document.addEventListener('turbo:before-visit', () => closeSearch(false), { signal });
  document.addEventListener('turbo:before-cache', () => {
    closeSearch(false);
    resetSearch();
  }, { signal });

  console.info('[SIOKA recherche] prête');
})();