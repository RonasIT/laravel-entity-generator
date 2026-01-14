<?php

namespace App\Http\Controllers;

use App\Http\Resources\Post\PostsCollectionResource;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\DeletePostRequest;
use App\Http\Requests\Post\GetPostRequest;
use App\Http\Requests\Post\SearchPostsRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Services\PostService;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function create(CreatePostRequest $request, PostService $service): PostResource
    {
        $data = $request->onlyValidated();

        $result = $service->create($data);

        return PostResource::make($result);
    }

    public function get(GetPostRequest $request, PostService $service, $id): PostResource
    {
        $result = $service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->find($id);

        return PostResource::make($result);
    }

    public function search(SearchPostsRequest $request, PostService $service): PostsCollectionResource
    {
        $result = $service->search($request->onlyValidated());

        return PostsCollectionResource::make($result);
    }

    public function update(UpdatePostRequest $request, PostService $service, $id): Response
    {
        $service->update($id, $request->onlyValidated());

        return response()->noContent();
    }

    public function delete(DeletePostRequest $request, PostService $service, $id): Response
    {
        $service->delete($id);

        return response()->noContent();
    }
}
