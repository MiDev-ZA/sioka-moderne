@extends('layouts.app')

@section('title', 'Toutes les vidéos - WebTV SIOKA')

@section('content')
@php
  $currentSort = request('sort', 'recent');
  $hasQuery    = request()->filled('q');
@endphp
<section class="sec webtv-sec" id="webtvSec">
  <div class="sec-head">
    <h2 class="sec-title">Toutes les vidéos</h2>
    <span class="webtv-count" aria-live="polite">{{ \Illuminate\Support\Number::format($videos->total()) }} vidéo(s)</span>
  </div>

  <form method="GET" action="{{ route('webtv.index') }}" class="webtv-filterbar" role="search"
        data-category="{{ request('category') }}">
    <div class="webtv-chips" role="navigation" aria-label="Catégories">
      <a href="{{ route('webtv.index', array_filter(['q' => request('q'), 'sort' => request('sort')])) }}"
         class="chip {{ !request()->filled('category') ? 'is-active' : '' }}" data-category="">Toutes</a>
      @foreach($categories ?? [] as $cat)
        <a href="{{ route('webtv.index', array_filter(['category' => $cat, 'q' => request('q'), 'sort' => request('sort')])) }}"
           class="chip {{ request('category') === $cat ? 'is-active' : '' }}" data-category="{{ $cat }}">{{ ucfirst($cat) }}</a>
      @endforeach
    </div>

    <div class="webtv-controls">
      <label class="webtv-search {{ $hasQuery ? 'has-value' : '' }}">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher une vidéo…" aria-label="Rechercher une vidéo" autocomplete="off">
        <button type="button" class="webtv-search-clear" aria-label="Effacer la recherche" @if(!$hasQuery) hidden @endif>
          <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
        <kbd class="webtv-kbd" aria-hidden="true">/</kbd>
      </label>

      <div class="webtv-segment" role="radiogroup" aria-label="Trier par">
        <label>
          <input type="radio" name="sort" value="recent" {{ $currentSort === 'recent' ? 'checked' : '' }}>
          <span>Plus récentes</span>
        </label>
        <label>
          <input type="radio" name="sort" value="popular" {{ $currentSort === 'popular' ? 'checked' : '' }}>
          <span>Plus vues</span>
        </label>
      </div>
    </div>

    <div id="webtvActive" class="webtv-active-slot">
      @include('partials.videos-active')
    </div>
  </form>

  <div id="webtvResults" class="webtv-results">
    @include('partials.videos-results')
  </div>
</section>

<script>
(function () {
  var root = document.getElementById('webtvSec');
  if (!root) return;

  var form       = root.querySelector('.webtv-filterbar');
  var input      = form.querySelector('input[name="q"]');
  var searchWrap = form.querySelector('.webtv-search');
  var clearBtn   = form.querySelector('.webtv-search-clear');
  var chips      = form.querySelectorAll('.chip');
  var results    = document.getElementById('webtvResults');
  var activeBox  = document.getElementById('webtvActive');
  var countEl    = root.querySelector('.webtv-count');
  var base       = form.getAttribute('action');

  var category   = form.dataset.category || '';
  var cache      = new Map();
  var CACHE_MS   = 60000;
  var controller = null;
  var timer      = null;

  function getSort() {
    var r = form.querySelector('input[name="sort"]:checked');
    return r ? r.value : 'recent';
  }

  function buildUrl() {
    var p = new URLSearchParams();
    var q = input.value.trim();
    if (q) p.set('q', q);
    if (category) p.set('category', category);
    if (getSort() !== 'recent') p.set('sort', getSort());
    var s = p.toString();
    return base + (s ? '?' + s : '');
  }

  function syncUI() {
    var has = input.value.length > 0;
    searchWrap.classList.toggle('has-value', has);
    clearBtn.hidden = !has;
    chips.forEach(function (c) {
      var on = (c.dataset.category || '') === category;
      c.classList.toggle('is-active', on);
      if (on) c.setAttribute('aria-current', 'true'); else c.removeAttribute('aria-current');
    });
  }

  function syncFromUrl(u) {
    var p = u.searchParams;
    input.value = p.get('q') || '';
    category = p.get('category') || '';
    var sort = p.get('sort') === 'popular' ? 'popular' : 'recent';
    var radio = form.querySelector('input[name="sort"][value="' + sort + '"]');
    if (radio) radio.checked = true;
    syncUI();
  }

  function apply(data) {
    results.innerHTML = data.results;
    activeBox.innerHTML = data.active;
    countEl.textContent = data.count;
    results.classList.remove('is-pending');
  }

  function fetchResults(url) {
    if (controller) controller.abort();
    var hit = cache.get(url);
    if (hit && Date.now() - hit.t < CACHE_MS) { apply(hit.data); return; }

    controller = new AbortController();
    results.classList.add('is-pending');

    fetch(url, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      signal: controller.signal
    })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (data) { cache.set(url, { t: Date.now(), data: data }); apply(data); })
      .catch(function (err) {
        if (err.name === 'AbortError') return;
        window.location.href = url; // repli : navigation classique
      });
  }

  function go() {
    var url = buildUrl();
    history.replaceState(history.state, '', url);
    syncUI();
    fetchResults(url);
  }

  // Recherche en direct : dès la première lettre
  input.addEventListener('input', function () {
    syncUI();
    clearTimeout(timer);
    timer = setTimeout(go, 100);
  });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && input.value) { input.value = ''; go(); }
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearTimeout(timer);
    go();
  });

  form.addEventListener('change', function (e) {
    if (e.target.name === 'sort') go();
  });

  root.addEventListener('click', function (e) {
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    if (e.target.closest('.webtv-search-clear')) {
      input.value = '';
      go();
      input.focus();
      return;
    }

    var a = e.target.closest('a');
    if (!a) return;

    if (a.classList.contains('chip')) {
      e.preventDefault();
      category = a.dataset.category || '';
      go();
      return;
    }

    var isPager = !!a.closest('.pagination-wrap');
    if (isPager || a.hasAttribute('data-ajax')) {
      e.preventDefault();
      var u = new URL(a.href, window.location.origin);
      syncFromUrl(u);
      history.replaceState(history.state, '', u.pathname + u.search);
      fetchResults(u.pathname + u.search);
      if (isPager) results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });

  // Raccourci "/" : focus dans la recherche (enregistré une seule fois)
  if (!window.__webtvSlash) {
    window.__webtvSlash = true;
    document.addEventListener('keydown', function (e) {
      var tag = (e.target.tagName || '').toLowerCase();
      if (e.key === '/' && tag !== 'input' && tag !== 'textarea' && tag !== 'select' && !e.target.isContentEditable) {
        var i = document.querySelector('.webtv-search input');
        if (i) { e.preventDefault(); i.focus(); i.select(); }
      }
    });
  }
})();
</script>
@endsection