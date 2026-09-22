<section class="sec" id="actualites" aria-labelledby="sec-recent">
  <div class="sec-head">
    <h2 class="sec-title" id="sec-recent">Actualités</h2>
    <a href="{{ Route::has('news.index') ? route('news.index') : '#' }}" class="sec-link" aria-label="Voir toutes les actualités">
      Tout voir <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    </a>
  </div>

  <div class="pills-bar" role="group" aria-label="Filtrer par catégorie" id="newsFilters">
    <button class="pill-btn on" data-filter="all">Tout</button>
    @foreach($categories as $category)
      <button class="pill-btn" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
    @endforeach
  </div>

  <div class="art-grid" id="newsGrid">
    @forelse($recentArticles as $article)
      @include('partials.article-card', ['article' => $article])
    @empty
      <p class="no-articles">Aucun article publié pour le moment.</p>
    @endforelse
  </div>

  @if($recentArticles->count() >= 6)
    <div class="load-more-wrap" style="text-align:center; margin-top:2rem;">
      <button type="button" id="loadMoreNews" class="btn-load-more" data-offset="6">
        Voir plus d'articles
      </button>
    </div>
  @endif
</section>

@push('scripts')
<script>
document.getElementById('loadMoreNews')?.addEventListener('click', async function () {
  const btn = this;
  const offset = parseInt(btn.dataset.offset, 10);
  const grid = document.getElementById('newsGrid');

  btn.disabled = true;
  btn.textContent = 'Chargement...';

  try {
    const response = await fetch(`{{ route('news.loadMore') }}?offset=${offset}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json();

    grid.insertAdjacentHTML('beforeend', data.html);
    btn.dataset.offset = offset + data.count;

    if (!data.hasMore) {
      btn.remove();
    } else {
      btn.disabled = false;
      btn.textContent = 'Voir plus d\'articles';
    }
  } catch (e) {
    btn.disabled = false;
    btn.textContent = 'Réessayer';
  }
});
</script>
@endpush