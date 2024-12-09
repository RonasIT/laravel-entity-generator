<?php

use App\Http\Controllers\PostController;


Route::controller(PostController::class)->group(function () {
    Route::post('posts', 'create');
    Route::put('posts/{id}', 'update');
    Route::delete('posts/{id}', 'delete');
    Route::get('posts/{id}', 'get');
    Route::get('posts', 'search');
});