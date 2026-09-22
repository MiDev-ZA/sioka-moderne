@extends('layouts.app')

@section('title', 'Toutes les vidéos - WebTV SIOKA')

@section('content')
<section class="sec webtv-sec">
  <div class="sec-head">
    <h2 class="sec-title">Toutes les vidéos</h2>
    <span class="webtv-count">{{ \Illuminate\Support\Number::format($videos->total()) }} vidéo(s)</span>
  </div>

  <form method="GET" action="{{ route('webtv.index') }}" class="webtv-filterbar" role="search"
        data-auth-loading="Chargement des vidéos…">
    <div class="webtv-chips">
      <a href="{{ route('webtv.index', array_filter(['q' => request('q'), 'sort' => request('sort')])) }}"
         class="chip {{ !request('category') ? 'is-active' : '' }}" data-page-loading="Chargement des vidéos…">Toutes</a>
      @foreach($categories ?? [] as $cat)
        <a href="{{ route('webtv.index', array_filter(['category' => $cat, 'q' => request('q'), 'sort' => request('sort')])) }}"
           class="chip {{ request('category') === $cat ? 'is-active' : '' }}" data-page-loading="Chargement des vidéos…">{{ ucfirst($cat) }}</a>
      @endforeach
    </div>

    <div class="webtv-controls">
      <label class="webtv-search">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher une vidéo…" aria-label="Rechercher une vidéo">
      </label>
      <select name="sort" class="webtv-sort" onchange="this.form.submit()" aria-label="Trier par">
        <option value="recent" {{ request('sort', 'recent') === 'recent' ? 'selected' : '' }}>Plus récentes</option>
        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Plus vues</option>
      </select>
      @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
    </div>
  </form>

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
      <a href="{{ route('webtv.index') }}" class="load-more-btn" data-page-loading="Chargement des vidéos…">Réinitialiser les filtres</a>
    </div>
  @endif
</section>
@endsection