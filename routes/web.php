<?php

use App\Http\Controllers\CommentsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [CommentsController::class, 'index'])->name('comments.index');
Route::post('comments', [CommentsController::class, 'store'])->name('comments.store');
Route::get('comments/create', [CommentsController::class, 'create'])->name('comments.create');
Route::get('comments/{comment}/edit', [CommentsController::class, 'edit'])->name('comments.edit');
Route::put('comments/{comment}', [CommentsController::class, 'update'])->name('comments.update');
Route::post('comments/preview', [CommentsController::class, 'preview']);
Route::get('comments/{comment}', [CommentsController::class, 'show'])->name('comments.show')->whereNumber('comment');
Route::get('/comments/{comment}/html', function (\App\Models\Comment $comment) {
    return view('comments.partials.comment', ['comment' => $comment])->render();
});
Route::get('/comments/listing', [CommentsController::class, 'listing'])->name('comments.listing');


