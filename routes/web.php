<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::post('/posts/{post}/vote', [PostController::class, 'vote'])->name('posts.vote');
Route::get('/posts/{post}/download', [PostController::class, 'download'])->name('posts.download');
Route::post('posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
Route::get('/wiki/{tag:name}', [TagController::class, 'wiki'])->name('tags.wiki');
Route::get('/tags/autocomplete', [TagController::class, 'autocomplete'])->name('tags.autocomplete');