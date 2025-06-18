<?php

use App\Http\Controllers\CommentsController;
use Illuminate\Support\Facades\Route;

Route::get('comments', [CommentsController::class, 'index']);
Route::post('comments', [CommentsController::class, 'store']);
Route::post('comments/preview', [CommentsController::class, 'preview']);
