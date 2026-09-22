@extends('layouts.app')

@section('content')
<section class="sec" id="actualites-toutes">
  <div class="sec-head">
    <h2 class="sec-title">Toutes les actualités</h2>
  </div>

  <div class="pills-bar" role="group" aria-label="Filtrer par catégorie" id="newsFilters">
    <button class="pill-btn on" data-filter="all">Tout</button>
    @foreach($categories as $category)
      <button class="pill-btn" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
    @endforeach
  </div>

  <div class="art-grid" id="newsGrid">
    @forelse($articles as $article)
      @include('partials.article-card', ['article' => $article])
    @empty
      <p class="no-articles">Aucun article publié pour le moment.</p>
    @endforelse
  </div>

  <div class="pagination-wrap" style="margin-top:2rem;">
    {{ $articles->links() }}
  </div>
</section>
@endsection