@if($videos->count() > 0)
  @php $isFirstPage = (int) request('page', 1) === 1; @endphp

  <div class="video-grid">
    @foreach($videos as $video)
      @include('partials.video-card', ['video' => $video, 'featured' => $loop->first && $isFirstPage, 'withDesc' => true])
    @endforeach
  </div>

  <div class="pagination-wrap">{{ $videos->appends(request()->query())->links() }}</div>
@else
  <div class="webtv-empty-state">
    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="48" height="48"><path d="M15 10l4.55-2.27A1 1 0 0 1 21 8.6v6.8a1 1 0 0 1-1.45.9L15 14M4 6h9a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/></svg>
    <p>Aucune vidéo ne correspond à votre recherche.</p>
    <a href="{{ route('webtv.index') }}" class="load-more-btn" data-ajax>Réinitialiser les filtres</a>
  </div>
@endif