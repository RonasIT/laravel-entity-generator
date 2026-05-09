<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Services\PostService;

class PostController extends Controller
{
    public function create(CreatePostRequest $request, PostService $service): PostResource
    {
        $data = $request->onlyValidated();

        $result = $service->create($data);

        return PostResource::make($result);
    }

}
