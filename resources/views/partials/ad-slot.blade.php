@php
  $adId = $adId ?? 'default';
  $format = $format ?? 'leaderboard';
  $ad = config('ads.enabled')
      ? \App\Models\Ad::displayable($adId)->inRandomOrder()->first()
      : null;
  if ($ad) { $ad->increment('impressions'); }
@endphp

@if($ad)
  <aside class="ad-slot ad-slot--{{ $format }}" data-ad-id="{{ $adId }}" aria-label="Publicité">
    <a class="ad-slot__content" href="{{ route('ads.click', $ad) }}" target="_blank" rel="sponsored noopener">
      <img src="{{ asset('storage/' . $ad->image) }}"
           alt="{{ $ad->image_alt ?? $ad->name }}"
           loading="{{ $adId === 'top-ticker' ? 'eager' : 'lazy' }}"
           onload="this.closest('.ad-slot__content').classList.add('is-loaded')"
           onerror="this.closest('.ad-slot').hidden = true">
    </a>
    <span class="ad-slot__label">Publicité</span>
  </aside>
@endif