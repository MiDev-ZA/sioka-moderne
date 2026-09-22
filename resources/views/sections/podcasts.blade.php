<section class="sec" id="podcasts" aria-labelledby="sec-podcasts">
  <div class="sec-head">
    <h2 class="sec-title" id="sec-podcasts">Podcasts</h2>
    <button type="button"
            class="sec-link"
            id="podcastsLibraryBtn"
            data-library-url="{{ route('podcasts.library') }}">
      Ouvrir la bibliothèque
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="m9 18 6-6-6-6"/>
      </svg>
    </button>
  </div>

  @if($podcasts->isNotEmpty())
    @php
      $featuredPodcast = $podcasts->first();
      $miniPodcasts = $podcasts->skip(1)->take(3);
    @endphp

    <div class="feat-grid">

      <article class="feat-big is-visible"
               tabindex="0"
               role="button"
               aria-label="Écouter : {{ $featuredPodcast->title }}"
               data-soundcloud-url="{{ $featuredPodcast->soundcloud_url }}">
        <img src="{{ !empty($featuredPodcast->image) ? asset('storage/' . $featuredPodcast->image) : asset('images/podcast-placeholder.jpg') }}"
             alt="{{ $featuredPodcast->image_alt ?? $featuredPodcast->title }}"
             onload="this.classList.add('is-loaded')">

        <div class="feat-big-content">
          <h2 class="feat-big-title">{{ $featuredPodcast->title }}</h2>
          <div class="feat-big-meta">{{ $featuredPodcast->user->name ?? 'SIOKA' }} · Diffusé {{ $featuredPodcast->created_at->diffForHumans() }}</div>
        </div>

        <div class="feat-play-badge feat-play-badge--big" aria-hidden="true">
          <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </div>

        <div class="feat-progress"><span class="feat-progress-fill"></span></div>
      </article>

      @if($miniPodcasts->isNotEmpty())
        <div class="feat-mini-list">
          @foreach($miniPodcasts as $podcast)
            <div class="feat-mini is-visible"
                 tabindex="0"
                 data-soundcloud-url="{{ $podcast->soundcloud_url }}">
              <button class="feat-mini-play {{ !empty($podcast->image) ? 'has-image' : '' }}" aria-label="Écouter" aria-pressed="false">
                @if(!empty($podcast->image))
                  <img src="{{ asset('storage/' . $podcast->image) }}" alt="" loading="lazy">
                  <span class="feat-mini-play-scrim" aria-hidden="true"></span>
                @endif
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
              </button>
              <div class="feat-mini-body">
                <div class="feat-mini-cat">{{ $podcast->user->name ?? 'SIOKA' }}</div>
                <div class="feat-mini-title">{{ $podcast->title }}</div>
                <div class="feat-mini-time">
                  <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> {{ $podcast->created_at->diffForHumans() }}
                  <span class="feat-eq" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                </div>
              </div>
              <div class="feat-progress"><span class="feat-progress-fill"></span></div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @else
    <div class="feat-empty">
      <p>Aucun podcast disponible pour le moment.</p>
    </div>
  @endif

  <!-- Modale : bibliothèque de podcasts, groupée par semaine -->
  <div class="library-modal-overlay" id="podcastsLibraryOverlay">
    <div class="library-modal-panel">
      <div class="library-modal-head">
        <h3 class="library-modal-title">Bibliothèque de podcasts</h3>
        <button type="button" class="library-modal-close" id="podcastsLibraryClose" aria-label="Fermer la bibliothèque">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="library-modal-body" id="podcastsLibraryBody">
        <div class="library-loading">
          <span class="library-spinner" aria-hidden="true"></span>
          Chargement des podcasts…
        </div>
      </div>
    </div>
  </div>
</section>