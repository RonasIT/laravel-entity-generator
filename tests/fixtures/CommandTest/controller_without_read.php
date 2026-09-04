<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\DeletePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Services\PostService;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends Controller
{
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
