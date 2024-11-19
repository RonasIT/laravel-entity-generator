<?php

use App\Http\Controllers\PostController;


Route::post('posts', [PostController::class, 'create']);
Route::put('posts/{id}', [PostController::class, 'update']);
Route::delete('posts/{id}', [PostController::class, 'delete']);
Route::get('posts/{id}', [PostController::class, 'get']);
Route::get('posts', [PostController::class, 'search']);
