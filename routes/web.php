<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FilController;
use App\Http\Controllers\FlashInfoController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\WebTvController;
use App\Http\Controllers\YoutubeAuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Page d'accueil (publique)
Route::get('/', [AppController::class, 'home'])->name('home');

// Affichage public d'un article
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

// Chargement AJAX des articles suivants (public, accessible à tous)
Route::get('/actualites/load-more', [AppController::class, 'loadMoreNews'])->name('news.loadMore');

Route::get('/toutes-actualites', [AppController::class, 'newsIndex'])->name('news.index');

// Articles publics d'une catégorie
Route::get('/categories/{category:slug}', [AppController::class, 'categoryShow'])->name('categories.show');

Route::get('/webtv/toutes-les-videos', [AppController::class, 'webtvIndex'])->name('webtv.index');

// Affichage public d'une vidéo WebTV
Route::get('/webtv/{id}', [WebTvController::class, 'show'])->name('webtv.show');

// Routes accessibles uniquement aux visiteurs
Route::middleware('guest')->group(function () {
    Route::get('/login', [AppController::class, 'login'])->name('login');
    Route::post('/login', [AppController::class, 'authenticate'])->name('login.post');
});

// Routes accessibles uniquement aux utilisateurs connectés
Route::middleware('auth')->group(function () {

    // ---- Communes à tous les comptes connectés ----
    Route::get('/dashboard', [AppController::class, 'dashboard'])->name('dashboard');
    Route::get('/lequipe', [AppController::class, 'lequipe'])->name('lequipe');
    Route::get('/profil', [AppController::class, 'profil'])->name('profil');
    Route::post('/logout', [AppController::class, 'logout'])->name('logout');

    // ---- Actualités, flash infos, fil d'infos : admin, editor, journalist ----
    Route::middleware('role:editor,journalist')->group(function () {
        Route::get('/actualites', [AppController::class, 'actualites'])->name('actualites');
        Route::get('/flash-infos', [AppController::class, 'flash_infos'])->name('flash-infos');
        Route::get('/fil-infos', [AppController::class, 'fil_infos'])->name('fil-infos');

        // articles actions
        Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
        Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
        Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');

        // Flash-infos actions
        Route::post('/flash-infos', [FlashInfoController::class, 'store'])->name('flash-infos.store');
        Route::put('/flash-infos/{flashInfo}', [FlashInfoController::class, 'update'])->name('flash-infos.update');
        Route::patch('/flash-infos/{flashInfo}/toggle', [FlashInfoController::class, 'toggle'])->name('flash-infos.toggle');
        Route::delete('/flash-infos/{flashInfo}', [FlashInfoController::class, 'destroy'])->name('flash-infos.destroy');

        // Fil-infos actions
        Route::post('/fil-en-continu', [FilController::class, 'store'])->name('fil-en-continu.store');
        Route::put('/fil-en-continu/{fil}', [FilController::class, 'update'])->name('fil-en-continu.update');
        Route::delete('/fil-en-continu/{fil}', [FilController::class, 'destroy'])->name('fil-en-continu.destroy');
        Route::patch('/fil-en-continu/{fil}/toggle', [FilController::class, 'toggle'])->name('fil-en-continu.toggle');
    });

    // ---- Émissions, podcasts : admin, editor, producer ----
    Route::middleware('role:editor,producer')->group(function () {
        Route::get('/emissions', [AppController::class, 'emissions'])->name('emissions');
        Route::get('/podcasts', [AppController::class, 'podcasts'])->name('podcasts');
        Route::get('/podcasts/bibliotheque', [AppController::class, 'podcastsLibrary'])->name('podcasts.library');

        // Podcasts actions
        Route::post('/podcasts', [PodcastController::class, 'store'])->name('podcasts.store');
        Route::put('/podcasts/{podcast}', [PodcastController::class, 'update'])->name('podcasts.update');
        Route::delete('/podcasts/{podcast}', [PodcastController::class, 'destroy'])->name('podcasts.destroy');
    });

    // ---- WebTV : admin et integrator uniquement ----
    Route::middleware('role:integrator')->group(function () {
        Route::get('/dashboard/webtv', [WebTvController::class, 'index'])->name('webtv');
        Route::post('/dashboard/webtv', [WebTvController::class, 'store'])->name('webtv.store');
        Route::post('/dashboard/webtv/upload-session', [WebTvController::class, 'uploadSession'])->name('webtv.upload-session');
        Route::post('/dashboard/webtv/upload-complete', [WebTvController::class, 'uploadComplete'])->name('webtv.upload-complete');
        Route::post('/dashboard/webtv/{videoId}/thumbnail', [WebTvController::class, 'setThumbnail'])->name('webtv.thumbnail');
        Route::put('/dashboard/webtv/{videoId}', [WebTvController::class, 'update'])->name('webtv.update');
        Route::delete('/dashboard/webtv/{videoId}', [WebTvController::class, 'destroy'])->name('webtv.destroy');
    });

    // ---- Newsletter : admin, editor ----
    Route::middleware('role:editor')->group(function () {
        Route::get('/abonnees-newsletter', [AppController::class, 'abonnees_newsletter'])->name('abonnees-newsletter');
    });

    // ---- Admin uniquement : connexion YouTube + utilisateurs ----
    Route::middleware('admin')->group(function () {
        Route::get('/auth/youtube', [YoutubeAuthController::class, 'redirect'])->name('youtube.auth');
        Route::get('/auth/youtube/callback', [YoutubeAuthController::class, 'callback'])->name('youtube.callback');

        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    });
});