<?php

use App\Http\Controllers\PostController;


Route::controller(PostController::class)->group(function () {
    Route::post('posts', 'create');
});