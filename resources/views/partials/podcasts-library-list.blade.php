@forelse($groups as $weekKey => $weekPodcasts)
  <div class="library-week">
    <h4 class="library-week-heading">{{ $weekLabels[$weekKey] }}</h4>
    <div class="library-week-list">
      @foreach($weekPodcasts as $podcast)
        <div class="feat-mini library-item"
             tabindex="0"
             data-soundcloud-url="{{ $podcast->soundcloud_url }}">
          <button class="feat-mini-play {{ !empty($podcast->image) ? 'has-image' : '' }}" aria-label="Écouter" aria-pressed="false">
            @if(!empty($podcast->image))
              <img src="{{ asset('storage/' . $podcast->image) }}" alt="" loading="lazy">
              <span class="feat-mini-play-scrim" aria-hidden="true"></span>
            @endif
            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
          </button>
          <div class="feat-mini-body">
            <div class="feat-mini-cat">{{ $podcast->user->name ?? 'SIOKA' }}</div>
            <div class="feat-mini-title">{{ $podcast->title }}</div>
            <div class="feat-mini-time">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> {{ $podcast->created_at->diffForHumans() }}
              <span class="feat-eq" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
            </div>
          </div>
          <div class="feat-progress"><span class="feat-progress-fill"></span></div>
        </div>
      @endforeach
    </div>
  </div>
@empty
  <p class="library-empty">Aucun podcast disponible pour le moment.</p>
@endforelse