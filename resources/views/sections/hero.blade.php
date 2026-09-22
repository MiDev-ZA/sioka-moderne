<section class="hero" aria-label="À la une SIOKA">
  
  <div class="crsl-wrap" id="carousel" role="region" aria-label="Diaporama des articles à la une" aria-roledescription="carousel">
    <div class="crsl-track" id="crslTrack">

        @foreach($featuredArticles as $index => $article)
            <div
                class="crsl-slide {{ $index == 0 ? 'is-active' : '' }}"
                role="group"
                aria-roledescription="slide"
                data-cat="{{ $article->category->name ?? 'Actualités' }}"
                data-cat-class=""
                data-live="false"
                data-title="{{ $article->title }}"
                data-excerpt="{{ $article->summary }}"
                data-author="{{ $article->user->name ?? 'Rédaction SIOKA' }}"
                data-time="{{ $article->created_at->diffForHumans() }}"
                data-read="{{ ceil(str_word_count(strip_tags($article->content))/200) }} min de lecture">

                <img
                    class="crsl-slide-bg"
                    src="{{ $article->image ? asset('storage/'.$article->image) : asset('images/default-news.jpg') }}"
                    alt="{{ $article->image_alt ?? $article->title }}">
            </div>
        @endforeach

    </div>

    <div class="crsl-glass" id="crslGlass" aria-live="polite">
        <div class="crsl-glass-top">
            <span class="cat-pill" id="crslCat">Politique</span>

            <span class="live-badge" id="crslLive" style="display:none;" aria-label="Alerte Info">
                <span class="live-dot" aria-hidden="true"></span>
                FLASH INFO
            </span>
        </div>

        <h2 class="crsl-title" id="crslTitle">
            Relance économique à Madagascar : Les nouvelles réformes présentées
        </h2>

        <p class="crsl-excerpt" id="crslExcerpt">
            Face aux défis actuels, une série de mesures stratégiques a été dévoilée...
        </p>

        <div class="crsl-glass-bottom">
            <div class="crsl-meta" id="crslMeta"></div>

            <div class="crsl-controls">
                <button class="crsl-arrow" id="crslPrev" aria-label="Précédent">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </button>

                <div class="crsl-dots" id="crslDots" role="tablist" aria-label="Navigation du diaporama">
                    @foreach($featuredArticles as $index => $article)
                        <button
                            class="crsl-dot {{ $index == 0 ? 'active' : '' }}"
                            role="tab"
                            aria-selected="{{ $index == 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $index + 1 }}">
                        </button>
                    @endforeach
                </div>

                <button class="crsl-arrow" id="crslNext" aria-label="Suivant">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
  </div>

  <aside class="hero-side" aria-label="Fil d'infos en continu">
    <div class="side-hd">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>

      <span class="side-hd-title">En continu</span>
    </div>

    <div class="news-feed-window">
      <div class="news-feed-list" id="verticalTicker">

        @forelse($fils as $fil)
            <a class="feed-item">
                <div class="feed-timeline">
                      <span class="feed-time">
                          {{ $fil->created_at->timezone('Indian/Antananarivo')->format('H:i') }}
                      </span>
                    <span class="feed-dot"></span>
                </div>

                <div class="feed-content">
                    <span class="feed-cat">
                        {{ $fil->category->name ?? 'Actualité' }}
                    </span>

                    <h3 class="feed-title">
                        {{ $fil->content }}
                    </h3>
                </div>
            </a>
        @empty
            <div class="feed-item">
                <div class="feed-content">
                    <span class="feed-cat">Information</span>
                    <h3 class="feed-title">
                        Aucun fil d'information disponible.
                    </h3>
                </div>
            </div>
        @endforelse

      </div>
    </div>
  </aside>
</section>