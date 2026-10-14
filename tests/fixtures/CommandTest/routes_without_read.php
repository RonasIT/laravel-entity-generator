<?php

use App\Http\Controllers\PostController;


Route::controller(PostController::class)->group(function () {
    Route::put('posts/{id}', 'update')->whereNumber('id');
    Route::delete('posts/{id}', 'delete')->whereNumber('id');
});