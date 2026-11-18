@extends('layouts.app')

@section('title', 'SIOKA - Za i\'Sioka rivo-baovao')

@section('content')

    @include('sections.hero')

    @include('sections.news')

    @include('partials.ad-slot', ['adId' => 'home-after-news', 'format' => 'leaderboard'])

    @include('sections.videos')

    @include('sections.podcasts')

    @include('partials.ad-slot', ['adId' => 'home-after-podcasts', 'format' => 'leaderboard'])

    @include('sections.newsletter')

    @include('sections.boites-aux-lettres')

@endsection