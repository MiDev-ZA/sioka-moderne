<a href="{{ route('articles.show', $article->slug) }}" class="art-card-link" data-page-loading="Chargement de l'article…">
  <article class="art-card" tabindex="-1" data-category="{{ $article->category->slug }}">
    <div class="art-card-img">
      <img src="{{ $article->image ? asset('storage/' . $article->image) : asset('images/placeholder-article.jpg') }}" alt="{{ $article->image_alt ?? $article->title }}" loading="lazy">
    </div>
    <div class="art-card-body">
      <div class="art-cat">{{ $article->category->name }}</div>
      <h3 class="art-title">{{ $article->title }}</h3>
      <p class="art-excerpt">{{ Str::limit($article->summary, 140) }}</p>
      <div class="art-foot">
        <div class="art-author">
          <div class="art-avatar">
            <img src="{{ $article->user->avatar_url ?? asset('images/default-avatar.png') }}" alt="Avatar {{ $article->user->name }}">
          </div>
          <span>{{ $article->user->name }}</span>
        </div>
        <span class="art-read">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
          {{ ceil(str_word_count(strip_tags($article->content)) / 200) }} min
        </span>
      </div>
    </div>
  </article>
</a>