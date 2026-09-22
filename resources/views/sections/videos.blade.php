<section class="sec webtv-sec" id="webtv" aria-labelledby="sec-webtv">
  <div class="sec-head">
    <h2 class="sec-title" id="sec-webtv">WebTV &amp; Archives Vidéos</h2>
    <div class="sec-head-links">
      <a href="{{ route('webtv.index') }}" class="sec-link" aria-label="Voir toutes les vidéos">
        Voir toutes les vidéos
      </a>
    </div>
  </div>

  @php
    $fmt = fn ($n) => \Illuminate\Support\Number::abbreviate($n ?? 0, precision: 1);
  @endphp

  @if($webtvIsLive)
    {{-- ===== Hero : direct en cours — mène vers la page de la vidéo ===== --}}
    <a href="{{ route('webtv.show', $webtvLive['id']) }}" class="live-card-link">
      <article class="live-card">
        <div class="live-media">
          <img src="{{ $webtvLive['thumbnail'] }}" alt="{{ $webtvLive['title'] }}" loading="lazy" decoding="async">
          <span class="media-scrim" aria-hidden="true"></span>
          <span class="onair" role="status">
            <span class="onair-rec" aria-hidden="true"></span>
            <span class="onair-label">En direct</span>
            <span class="onair-clock" data-live-clock aria-hidden="true">00:00:00</span>
          </span>
          @if($webtvLive['viewers'])
            <span class="viewers">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
              {{ $fmt($webtvLive['viewers']) }} spectateurs
            </span>
          @endif
          <span class="play xl"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
        </div>
        <div class="hero-info">
          <span class="eyebrow">En ce moment</span>
          <h3 class="hero-title">{{ $webtvLive['title'] }}</h3>
          <p class="hero-desc">Suivez l'actualité malgache en direct sur la WebTV de Sioka Radio.</p>
        </div>
      </article>
    </a>
  @elseif($webtvHero)
    {{-- ===== Hero : dernière publication — mène vers la page de la vidéo ===== --}}
    <a href="{{ route('webtv.show', $webtvHero['id']) }}" class="live-card-link">
      <article class="live-card">
        <div class="live-media">
          <img src="{{ $webtvHero['thumbnail'] }}" alt="{{ $webtvHero['title'] }}" loading="lazy" decoding="async">
          <span class="media-scrim" aria-hidden="true"></span>
          <span class="onair replay">
            <span class="onair-dot" aria-hidden="true"></span>
            <span class="onair-label">Dernière publication</span>
          </span>
          <span class="viewers">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            {{ $fmt($webtvHero['views']) }} vues
          </span>
          <span class="play xl"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
        </div>
        <div class="hero-info">
          <span class="eyebrow">{{ ucfirst($webtvHero['category']) }}</span>
          <h3 class="hero-title">{{ $webtvHero['title'] }}</h3>
          <p class="hero-desc">{{ \Illuminate\Support\Str::limit($webtvHero['description'], 140) }}</p>
          <span class="bulletin-meta">{{ \Carbon\Carbon::parse($webtvHero['publishedAt'])->translatedFormat('d F Y') }} · {{ $webtvHero['duration'] }}</span>
        </div>
      </article>
    </a>
  @endif

  @if(!empty($webtvRecent))
    {{-- ===== Vidéos récentes — chaque carte mène vers sa page dédiée ===== --}}
    <div class="webtv-divider">
      <span class="webtv-divider-label">Vidéos récentes</span>
      <span class="webtv-divider-rule" aria-hidden="true"></span>
    </div>
    <div class="video-grid">
      @foreach($webtvRecent as $video)
        @include('partials.video-card', ['video' => $video, 'featured' => $loop->first])
      @endforeach
    </div>

    <div class="webtv-loadmore">
      <a href="{{ route('webtv.index') }}" class="load-more-btn">
        Voir toutes les vidéos
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
      </a>
    </div>
  @elseif(!$webtvIsLive && !$webtvHero)
    <p class="webtv-empty">Aucune vidéo disponible pour le moment.</p>
  @endif
</section>

@push('scripts')
<script>
document.addEventListener('turbo:load', function () {
  function tickClocks() {
    const now = new Date();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');
    document.querySelectorAll('[data-live-clock]').forEach(el => {
      el.textContent = `${hh}:${mm}:${ss}`;
    });
  }
  tickClocks();
  setInterval(tickClocks, 1000);
});
</script>
@endpush