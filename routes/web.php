<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentsPageController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::post('/posts/{post}/vote', [PostController::class, 'vote'])->name('posts.vote');
Route::post('/posts/{post}/thumbnail', [PostController::class, 'updateThumbnail'])->name('posts.thumbnail')->middleware('auth');
Route::get('/posts/{post}/download', [PostController::class, 'download'])->name('posts.download');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store')->middleware('auth');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy')->middleware('auth');
Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update')->middleware('auth');
Route::post('/comments/{comment}/vote', [CommentController::class, 'vote'])->name('comments.vote')->middleware('auth');
Route::post('/posts/{post}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle')->middleware('auth');
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index')->middleware('auth');
Route::post('/comments/upload-image', [CommentController::class, 'uploadImage'])->name('comments.upload-image')->middleware('auth');
Route::get('/comments', [CommentsPageController::class, 'index'])->name('comments.index');
Route::get('/comments/search', [CommentsPageController::class, 'search'])->name('comments.search');
Route::get('/wiki/{tag:name}', [TagController::class, 'wiki'])->name('tags.wiki');
Route::get('/tags/autocomplete', [TagController::class, 'autocomplete'])->name('tags.autocomplete');

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/changes', [NoteController::class, 'changes'])->name('notes.changes');
Route::post('/posts/{post}/notes', [NoteController::class, 'store'])->name('notes.store')->middleware('auth');
Route::post('/notes/preview', [NoteController::class, 'previewBody'])->name('notes.preview')->middleware('auth');
Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update')->middleware('auth');
Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy')->middleware('auth');
Route::get('/notes/{note}/history', [NoteController::class, 'history'])->name('notes.history');
Route::post('/notes/{note}/versions/{version}/revert', [NoteController::class, 'revert'])->name('notes.revert')->middleware('auth');

Route::get('/upload', [UploadController::class, 'create'])->name('upload.create')->middleware('auth');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store')->middleware('auth');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/account', [AccountController::class, 'show'])->name('account.show')->middleware('auth');
Route::post('/account/avatar', [AccountController::class, 'updateAvatar'])->name('account.avatar')->middleware('auth');

Route::get('/users/autocomplete', [UserController::class, 'autocomplete'])->name('users.autocomplete');
Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');