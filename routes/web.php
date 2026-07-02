<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AppController::class, 'home'])->name('home');
Route::get('/login', [AppController::class, 'login'])->name('login');
Route::get('/dashboard', [AppController::class, 'dashboard'])->name('dashboard');