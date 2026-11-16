<div class="search-overlay" id="searchOverlay" role="dialog" aria-modal="true" aria-label="Recherche"
  data-endpoint="{{ url('/recherche') }}">
  <div class="search-box" id="searchBox">
    <div class="search-field">
      <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="search" class="search-input" id="searchInput" placeholder="Rechercher un article, une vidéo, un podcast…" autocomplete="off" spellcheck="false" aria-label="Rechercher" aria-controls="searchResults">
      <button class="search-close-btn" id="searchClose" type="button" aria-label="Fermer la recherche">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <span class="search-progress" aria-hidden="true"></span>
    </div>

    <div class="search-results" id="searchResults" aria-live="polite">
      <p class="search-status">Tapez au moins 2 caractères pour chercher un article, une vidéo ou un podcast.</p>
    </div>

    <p class="search-hint">
    </p>
  </div>
</div>
