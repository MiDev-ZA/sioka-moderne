@foreach($articles as $article)
  @include('partials.article-card', ['article' => $article])
@endforeach