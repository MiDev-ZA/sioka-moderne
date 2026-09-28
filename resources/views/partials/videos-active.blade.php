@php
  $currentSort = request('sort', 'recent');
  $hasQuery    = request()->filled('q');
  $hasCategory = request()->filled('category');
  $hasFilters  = $hasQuery || $hasCategory || $currentSort !== 'recent';
@endphp
@if($hasFilters)
  <div class="webtv-active">
    <span class="webtv-active-label">Filtres actifs</span>
    @if($hasCategory)
      <a href="{{ route('webtv.index', array_filter(['q' => request('q'), 'sort' => request('sort')])) }}" class="active-tag" data-ajax>
        {{ ucfirst(request('category')) }}
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        <span class="sr-only">Retirer la catégorie</span>
      </a>
    @endif
    @if($hasQuery)
      <a href="{{ route('webtv.index', array_filter(['category' => request('category'), 'sort' => request('sort')])) }}" class="active-tag" data-ajax>
        « {{ request('q') }} »
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        <span class="sr-only">Retirer la recherche</span>
      </a>
    @endif
    @if($currentSort !== 'recent')
      <a href="{{ route('webtv.index', array_filter(['category' => request('category'), 'q' => request('q')])) }}" class="active-tag" data-ajax>
        Plus vues
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        <span class="sr-only">Retirer le tri</span>
      </a>
    @endif
    <a href="{{ route('webtv.index') }}" class="webtv-reset" data-ajax>Tout effacer</a>
  </div>
@endif