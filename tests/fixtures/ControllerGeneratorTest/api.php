<?php

use App\Http\Controllers\PostController;


Route::controller(PostController::class)->group(function () {
    Route::post('posts', 'create');
    Route::put('posts/{id}', 'update')->whereNumber('id');
    Route::delete('posts/{id}', 'delete')->whereNumber('id');
    Route::get('posts/{id}', 'get')->whereNumber('id');
    Route::get('posts', 'search');
});