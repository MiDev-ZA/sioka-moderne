@extends('layouts.app')

@section('content')
<article class="art-single">
  <div class="art-single-hero">
    <img src="{{ $article->image ? asset('storage/' . $article->image) : asset('images/placeholder-article.jpg') }}"
         alt="{{ $article->image_alt ?? $article->title }}">
    @if($article->image_caption)
      <p class="art-single-caption">{{ $article->image_caption }}</p>
    @endif
  </div>

  <div class="art-single-body">
    <div class="art-cat">{{ $article->category->name }}</div>
    <h1 class="art-single-title">{{ $article->title }}</h1>

    <div class="art-foot" style="margin-bottom:2rem;">
      <div class="art-author">
        <div class="art-avatar">
          <img src="{{ asset('images/default-avatar.png') }}" alt="Avatar {{ $article->user->name }}">
        </div>
        <span>{{ $article->user->name }}</span>
      </div>
      <span class="art-read">{{ $article->created_at->format('d/m/Y') }}</span>
    </div>

    <div class="art-single-content">
      {!! $article->content !!}
    </div>
  </div>
</article>

@if($relatedArticles->isNotEmpty())
<section class="sec art-related">
  <div class="sec-head">
    <h2 class="sec-title">Articles similaires</h2>
  </div>

  <div class="art-grid">
    @foreach($relatedArticles as $related)
      @include('partials.article-card', ['article' => $related])
    @endforeach
  </div>
</section>
@endif
@endsection