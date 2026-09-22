{{--
  Partiel réutilisé par videos.blade.php (accueil), videos-all.blade.php (toutes les vidéos)
  et webtv/show.blade.php (vidéos similaires) — même logique que partials.article-card.

  Attend : $video (array avec id, title, thumbnail, duration, category, views, publishedAt, description)
  Optionnel : $featured (bool), $withDesc (bool, affiche l'extrait de description)
--}}
@php
  $catColor = fn ($cat) => match(\Illuminate\Support\Str::slug($cat)) {
    'sport' => 'vert',
    'culture' => 'or',
    'politique' => 'magenta',
    default => 'bleu',
  };
  $isNew = \Carbon\Carbon::parse($video['publishedAt'])->diffInHours(now()) < 48;
@endphp
<a href="{{ route('webtv.show', $video['id']) }}" class="video-card-link" data-page-loading="Chargement de la vidéo…">
  <article class="video-card {{ ($featured ?? false) ? 'is-featured' : '' }}">
    <span class="cat-stripe" data-color="{{ $catColor($video['category']) }}" aria-hidden="true"></span>
    <div class="video-thumb">
      <img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] }}" loading="lazy" decoding="async">
      <span class="media-scrim" aria-hidden="true"></span>
      @if($isNew)
        <span class="video-new">Nouveau</span>
      @endif
      <span class="mini-play"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
      <span class="timecode">{{ $video['duration'] }}</span>
    </div>
    <div class="video-body">
      <span class="tag" data-color="{{ $catColor($video['category']) }}">{{ ucfirst($video['category']) }}</span>
      <h3 class="video-title"><span>{{ $video['title'] }}</span></h3>
      @if($withDesc ?? false)
        <p class="video-desc">{{ \Illuminate\Support\Str::limit($video['description'] ?? '', 90) }}</p>
      @endif
      <div class="video-meta">
        <span class="video-date">{{ \Carbon\Carbon::parse($video['publishedAt'])->translatedFormat('d M Y') }}</span>
        <span class="video-views">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
          {{ \Illuminate\Support\Number::abbreviate($video['views'] ?? 0, precision: 1) }}
        </span>
      </div>
    </div>
  </article>
</a>