<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::post('/posts/{post}/vote', [PostController::class, 'vote'])->name('posts.vote');
Route::get('/posts/{post}/download', [PostController::class, 'download'])->name('posts.download');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store')->middleware('auth');
Route::post('/posts/{post}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle')->middleware('auth');
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index')->middleware('auth');
Route::post('/comments/upload-image', [CommentController::class, 'uploadImage'])->name('comments.upload-image')->middleware('auth');
Route::get('/wiki/{tag:name}', [TagController::class, 'wiki'])->name('tags.wiki');
Route::get('/tags/autocomplete', [TagController::class, 'autocomplete'])->name('tags.autocomplete');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/account', [AccountController::class, 'show'])->name('account.show')->middleware('auth');