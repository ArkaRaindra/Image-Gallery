<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/wiki_pages/{tag:name', [TagController::class, 'wiki'])->name('tags.wiki');
Route::get('/tags/autocomplete', [TagController::class, 'autocomplete'])->name('tags.autocomplete');
