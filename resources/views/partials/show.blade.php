@extends('layouts.app')

@section('title', $video['title'] . ' - WebTV SIOKA')

@section('content')
@php
  $catColor = match(\Illuminate\Support\Str::slug($video['category'])) {
    'sport' => 'vert',
    'culture' => 'or',
    'politique' => 'magenta',
    default => 'bleu',
  };
@endphp

<article class="video-single">
  <div class="video-single-player">
    <iframe
      src="https://www.youtube.com/embed/{{ $video['id'] }}?autoplay=1&rel=0"
      title="{{ $video['title'] }}"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
      allowfullscreen></iframe>
  </div>

  <div class="video-single-info">
    <span class="video-single-eyebrow" data-color="{{ $catColor }}">{{ ucfirst($video['category']) }}</span>
    <h1 class="video-single-title">{{ $video['title'] }}</h1>
    <div class="video-single-meta">
      <span>{{ \Carbon\Carbon::parse($video['publishedAt'])->translatedFormat('d F Y') }}</span>
      <span>{{ \Illuminate\Support\Number::abbreviate($video['views'] ?? 0, precision: 1) }} vues</span>
      <span>{{ $video['duration'] }}</span>
    </div>
    @if(!empty($video['description']))
      <p class="video-single-desc">{{ $video['description'] }}</p>
    @endif
  </div>
</article>

@if(!empty($relatedVideos))
  <section class="video-related">
    <div class="sec-head">
      <h2 class="sec-title">À regarder aussi</h2>
    </div>
    <div class="video-grid">
      @foreach($relatedVideos as $related)
        @include('partials.video-card', ['video' => $related])
      @endforeach
    </div>
  </section>
@endif
@endsection