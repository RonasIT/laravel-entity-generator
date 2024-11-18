<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostsCollectionResource;
use App\Http\Requests\Posts\CreatePostRequest;
use App\Http\Requests\Posts\DeletePostRequest;
use App\Http\Requests\Posts\GetPostRequest;
use App\Http\Requests\Posts\SearchPostsRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function create(CreatePostRequest $request, PostService $service)
    {
        $data = $request->onlyValidated();

        $result = $service->create($data);

        return PostResource::make($result);
    }

    public function get(GetPostRequest $request, PostService $service, $id)
    {
        $result = $service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->find($id);

        return PostResource::make($result);
    }

    public function search(SearchPostsRequest $request, PostService $service)
    {
        $result = $service->search($request->onlyValidated());

        return PostsCollectionResource::make($result);
    }

    public function update(UpdatePostRequest $request, PostService $service, $id)
    {
        $service->update($id, $request->onlyValidated());

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function delete(DeletePostRequest $request, PostService $service, $id)
    {
        $service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}